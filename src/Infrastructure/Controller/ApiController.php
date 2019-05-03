<?php

namespace App\Infrastructure\Controller;

use App\Domain\CitizenFleetGeneratorInterface;
use App\Domain\CitizenNumber;
use App\Domain\Exception\BadCitizenException;
use App\Domain\Exception\FleetUploadedTooCloseException;
use App\Domain\Exception\InvalidFleetDataException;
use App\Domain\Exception\NotFoundHandleSCException;
use App\Domain\FleetUploadHandlerInterface;
use App\Domain\OrganisationFleetGeneratorInterface;
use App\Domain\SpectrumIdentification;
use App\Domain\User;
use App\Infrastructure\Form\Dto\FleetUpload;
use App\Infrastructure\Form\FleetUploadForm;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiController extends AbstractController
{
    private $logger;
    private $translator;
    private $security;
    private $fleetUploadHandler;

    public function __construct(
        LoggerInterface $logger,
        TranslatorInterface $translator,
        Security $security,
        FleetUploadHandlerInterface $fleetUploadHandler
    ) {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->security = $security;
        $this->fleetUploadHandler = $fleetUploadHandler;
    }

    /**
     * @Route("/me", name="me", methods={"GET"})
     */
    public function me(): Response
    {
        $user = $this->getUser();

        return $this->json($user, 200, [], ['groups' => 'me:read']);
    }

    /**
     * @Route("/export", name="export", methods={"POST"}, condition="request.getContentType() == 'json'")
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

        /** @var User $user */
        $user = $this->security->getUser();
        if ($user->citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/#/profile">profile page</a>.',
            ], 400);
        }

        try {
            $this->fleetUploadHandler->handle($user->citizen, $fleetData);
        } catch (FleetUploadedTooCloseException $e) {
            return $this->json([
                'error' => 'uploaded_too_close',
                'errorMessage' => 'Your fleet has been uploaded recently. Please wait before re-uploading.',
            ], 400);
        } catch (NotFoundHandleSCException $e) {
            return $this->json([
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The SC handle %s does not exist.', $user->citizen->actualHandle),
            ], 400);
        } catch (BadCitizenException $e) {
            return $this->json([
                'error' => 'bad_citizen',
                'errorMessage' => sprintf('Your SC handle has probably changed. Please update it in <a href="/#/profile">your Profile</a>.'),
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
     * @Route("/upload", name="upload", methods={"POST"})
     *
     * Upload star citizen fleet for one user.
     */
    public function upload(
        Request $request,
        FormFactoryInterface $formFactory): Response
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
        $fleetData = \json_decode($fleetFileContents, true);
        if (JSON_ERROR_NONE !== $jsonError = json_last_error()) {
            $this->logger->error('Failed to decode json from fleet file', ['json_error' => $jsonError, 'fleet_file_contents' => $fleetFileContents]);

            return $this->json([
                'error' => 'bad_json',
                'errorMessage' => sprintf('Your fleet file is not JSON well formatted. Please check it.'),
            ], 400);
        }

        /** @var User $user */
        $user = $this->security->getUser();
        if ($user->citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/#/profile">profile page</a>.',
            ], 400);
        }

        try {
            $this->fleetUploadHandler->handle($user->citizen, $fleetData);
        } catch (FleetUploadedTooCloseException $e) {
            return $this->json([
                'error' => 'uploaded_too_close',
                'errorMessage' => 'Your fleet has been uploaded recently. Please wait before re-uploading.',
            ], 400);
        } catch (NotFoundHandleSCException $e) {
            return $this->json([
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The SC handle %s does not exist.', $user->citizen->actualHandle),
            ], 400);
        } catch (BadCitizenException $e) {
            return $this->json([
                'error' => 'bad_citizen',
                'errorMessage' => sprintf('Your SC handle has probably changed. Please update it in <a href="/#/profile">your Profile</a>.'),
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
     * @Route("/create-citizen-fleet-file/{citizenNumber}", name="create_citizen_fleet_file", methods={"GET"})
     *
     * Combines the last version fleet of the given citizen.
     * Returns a downloadable json file.
     */
    public function createCitizenFleetFile(
        Request $request,
        string $citizenNumber,
        CitizenFleetGeneratorInterface $citizenFleetGenerator): Response
    {
        try {
            $file = $citizenFleetGenerator->generateFleetFile(new CitizenNumber($citizenNumber));
        } catch (\Exception $e) {
            throw $this->createNotFoundException('The fleet file could not be generated.');
        }
        $filename = 'citizen_fleet.json';

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'application/json');
        $response->deleteFileAfterSend();
        $response::trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            $filename
        );

        return $response;
    }

    /**
     * @Route("/create-organisation-fleet-file/{organisation}", name="create_organisation_fleet_file", methods={"GET"})
     *
     * Combines all last version fleets of all citizen members of a specific organisation.
     * Returns a downloadable json file.
     */
    public function createOrganisationFleetFile(
        Request $request,
        string $organisation,
        OrganisationFleetGeneratorInterface $fleetGenerator): Response
    {
        $file = $fleetGenerator->generateFleetFile(new SpectrumIdentification($organisation));
        $filename = 'organisation_fleet.json';

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'application/json');
        $response->deleteFileAfterSend();
        $response::trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            $filename
        );

        return $response;
    }
}
