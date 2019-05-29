<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Fleet;
use App\Entity\User;
use App\Exception\BadCitizenException;
use App\Exception\FleetUploadedTooCloseException;
use App\Exception\InvalidFleetDataException;
use App\Exception\NotFoundHandleSCException;
use App\Form\Dto\FleetUpload;
use App\Form\FleetUploadForm;
use App\Repository\CitizenRepository;
use App\Service\CitizenFleetGenerator;
use App\Service\FleetUploadHandler;
use App\Service\OrganisationFleetGenerator;
use App\Service\OrganizationInfosProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    private $logger;
    private $security;
    private $fleetUploadHandler;
    private $citizenFleetGenerator;
    private $organisationFleetGenerator;
    private $organizationInfosProvider;
    private $citizenRepository;
    private $serializer;

    public function __construct(
        LoggerInterface $logger,
        Security $security,
        FleetUploadHandler $fleetUploadHandler,
        CitizenFleetGenerator $citizenFleetGenerator,
        OrganisationFleetGenerator $organisationFleetGenerator,
        OrganizationInfosProviderInterface $organizationInfosProvider,
        CitizenRepository $citizenRepository,
        SerializerInterface $serializer
    ) {
        $this->logger = $logger;
        $this->security = $security;
        $this->fleetUploadHandler = $fleetUploadHandler;
        $this->citizenFleetGenerator = $citizenFleetGenerator;
        $this->organisationFleetGenerator = $organisationFleetGenerator;
        $this->organizationInfosProvider = $organizationInfosProvider;
        $this->citizenRepository = $citizenRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/me", name="me", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function me(): Response
    {
        return $this->json($this->security->getUser(), 200, [], ['groups' => 'me:read']);
    }

    /**
     * @Route("/my-orgas", name="my_orgas", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function myOrganizations(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([]);
        }

        $res = [];
        foreach ($citizen->getOrganisations() as $sid) {
            $res[] = $this->organizationInfosProvider->retrieveInfos(new SpectrumIdentification($sid));
        }

        return $this->json($res);
    }

    /**
     * Preflight CORS request.
     *
     * @Route("/export", name="export_options", methods={"OPTIONS"})
     */
    public function exportOptions(): Response
    {
        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/export", name="export", methods={"POST"}, condition="request.getContentType() == 'json'")
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function export(Request $request): Response
    {
        $contents = $request->getContent();
        $fleetData = \json_decode($contents, true);

        if (JSON_ERROR_NONE !== $jsonError = json_last_error()) {
            $this->logger->error('Failed to decode json from fleet file', ['json_error' => $jsonError, 'fleet_file_contents' => $contents]);

            return $this->json([
                'error' => 'bad_json',
                'errorMessage' => sprintf('Your fleet file is not JSON well formatted. Please check it.'),
            ], 400);
        }

        return $this->handleFleetData($fleetData);
    }

    /**
     * Upload star citizen fleet for one user.
     *
     * @Route("/upload", name="upload", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function upload(Request $request, FormFactoryInterface $formFactory): Response
    {
        $fleetUpload = new FleetUpload();
        $form = $formFactory->createNamedBuilder('', FleetUploadForm::class, $fleetUpload)->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'error' => 'not_submitted_form',
                'errorMessage' => 'No data has been submitted.',
            ], 400);
        }
        if (!$form->isValid()) {
            $formErrors = $form->getErrors(true);
            $errors = [];
            foreach ($formErrors as $formError) {
                $errors[] = $formError->getMessage();
            }
            $this->logger->warning('Upload fleet form error.', [
                'form_errors' => $errors,
            ]);

            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $fleetFileContents = file_get_contents($fleetUpload->fleetFile->getRealPath());
        $fleetFileContents = str_replace("\xEF\xBB\xBF", '', $fleetFileContents); // remove the utf-8 BOM
        $fleetData = \json_decode($fleetFileContents, true);
        if (JSON_ERROR_NONE !== $jsonError = json_last_error()) {
            $this->logger->error('Failed to decode json from fleet file', ['json_error' => $jsonError, 'fleet_file_contents' => $fleetFileContents]);

            return $this->json([
                'error' => 'bad_json',
                'errorMessage' => sprintf('Your fleet file is not JSON well formatted. Please check it.'),
            ], 400);
        }

        return $this->handleFleetData($fleetData);
    }

    private function handleFleetData(array $fleetData): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }

        try {
            $this->fleetUploadHandler->handle($citizen, $fleetData);
        } catch (FleetUploadedTooCloseException $e) {
            return $this->json([
                'error' => 'uploaded_too_close',
                'errorMessage' => 'Your fleet has been uploaded recently. Please wait before re-uploading.',
            ], 400);
        } catch (NotFoundHandleSCException $e) {
            return $this->json([
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The SC handle %s does not exist.', $citizen->getActualHandle()),
                'context' => ['handle' => $citizen->getActualHandle()],
            ], 400);
        } catch (BadCitizenException $e) {
            return $this->json([
                'error' => 'bad_citizen',
                'errorMessage' => sprintf('Your SC handle has probably changed. Please update it in <a href="/profile/">your Profile</a>.'),
            ], 400);
        } catch (InvalidFleetDataException $e) {
            return $this->json([
                'error' => 'invalid_fleet_data',
                'errorMessage' => sprintf('The fleet data in your file is invalid. Please check it.'),
            ], 400);
        } catch (\Exception $e) {
            $this->logger->error('cannot handle fleet file', ['exception' => $e]);

            return $this->json([
                'error' => 'cannot_handle_file',
                'errorMessage' => 'Cannot handle the fleet file. Try again !',
            ], 400);
        }

        return $this->json(null, 204);
    }

    /**
     * Combines the last version fleet of the given citizen.
     * Returns a downloadable json file.
     *
     * @Route("/create-citizen-fleet-file", name="create_citizen_fleet_file", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function createCitizenFleetFile(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            throw $this->createNotFoundException(sprintf('The user "%s" has no citizens.', $user->getId()));
        }

        try {
            $file = $this->citizenFleetGenerator->generateFleetFile($citizen->getNumber());
        } catch (\Exception $e) {
            throw $this->createNotFoundException('The fleet file could not be generated.');
        }
        $filename = 'citizen_fleet.json';

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'application/json');
        $response->deleteFileAfterSend();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            $filename
        );

        return $response;
    }

    /**
     * Combines all last version fleets of all citizen members of a specific organisation.
     * Returns a downloadable json file.
     *
     * @Route("/create-organisation-fleet-file/{organization}", name="create_organisation_fleet_file", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function createOrganisationFleetFile(string $organization): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            throw $this->createNotFoundException(sprintf('The user "%s" has no citizens.', $user->getId()));
        }
        if (!$citizen->hasOrganisation($organization)) {
            throw $this->createNotFoundException(sprintf('The citizen "%s" does not have the organization "%s".', $citizen->getId(), $organization));
        }

        $file = $this->organisationFleetGenerator->generateFleetFile(new SpectrumIdentification($organization));
        $filename = 'organisation_fleet.json';

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'application/json');
        $response->deleteFileAfterSend();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            $filename
        );

        return $response;
    }

    /**
     * @Route("/export-orga-fleet/{organization}", name="export_orga_fleet", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function exportOrgaFleet(string $organization): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            throw $this->createNotFoundException(sprintf('The user "%s" has no citizens.', $user->getId()));
        }
        if (!$citizen->hasOrganisation($organization)) {
            throw $this->createNotFoundException(sprintf('The citizen "%s" does not have the organization "%s".', $citizen->getId(), $organization));
        }

        $citizens = $this->citizenRepository->getByOrganisation(new SpectrumIdentification($organization));

        $ships = [];
        $totalColumn = [];
        foreach ($citizens as $citizen) {
            $citizenHandle = $citizen->getActualHandle()->getHandle();
            $lastFleet = $citizen->getLastVersionFleet();
            if ($lastFleet === null) {
                continue;
            }
            foreach ($lastFleet->getShips() as $ship) {
                if (!isset($ships[$ship->getName()])) {
                    $ships[$ship->getName()] = [$citizenHandle => 1];
                } elseif (!isset($ships[$ship->getName()][$citizenHandle])) {
                    $ships[$ship->getName()][$citizenHandle] = 1;
                } else {
                    ++$ships[$ship->getName()][$citizenHandle];
                }
            }
        }
        ksort($ships);

        $data = [];
        foreach ($ships as $shipName => $owners) {
            $total = 0;
            $columns = [];
            foreach ($owners as $ownerName => $countOwner) {
                $total += $countOwner;
                $columns[$ownerName] = $countOwner;
                if (!isset($totalColumn[$ownerName])) {
                    $totalColumn[$ownerName] = $countOwner;
                } else {
                    $totalColumn[$ownerName] += $countOwner;
                }
            }
            $data[] = array_merge([
                'Ship Model' => $shipName,
                'Ship Total' => $total,
            ], $columns);
        }

        $total = 0;
        $columns = [];
        foreach ($totalColumn as $ownerName => $countOwner) {
            $total += $countOwner;
            $columns[$ownerName] = $countOwner;
        }
        $data[] = array_merge([
            'Ship Model' => 'Total',
            'Ship Total' => $total,
        ], $columns);

        $csv = $this->serializer->serialize($data, 'csv');
        $filepath = sys_get_temp_dir().'/'.uniqid('', true);
        file_put_contents($filepath, $csv);

        $file = $this->file($filepath, 'export_'.$organization.'.csv');
        $file->deleteFileAfterSend();

        return $file;
    }

    /**
     * @Route("/numbers", name="numbers", methods={"GET"})
     */
    public function numbers(EntityManagerInterface $entityManager): Response
    {
        $citizens = $entityManager->getRepository(Citizen::class)->findAll();

        $orgas = [];
        foreach ($citizens as $citizen) {
            $orgas = array_merge($orgas, $citizen->getOrganisations());
        }
        $orgas = array_unique($orgas);

        $users = $entityManager->getRepository(User::class)->findAll();

        $fleets = $entityManager->getRepository(Fleet::class)->getLastVersionFleets();
        $countShips = 0;
        foreach ($fleets as $fleet) {
            $countShips += count($fleet[0]->getShips());
        }

        return $this->json([
            'organizations' => count($orgas),
            'users' => count($users),
            'ships' => $countShips,
        ]);
    }
}
