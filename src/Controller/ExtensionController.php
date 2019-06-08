<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\BadCitizenException;
use App\Exception\FleetUploadedTooCloseException;
use App\Exception\InvalidFleetDataException;
use App\Exception\NotFoundHandleSCException;
use App\Form\Dto\FleetUpload;
use App\Form\FleetUploadForm;
use App\Service\FleetUploadHandler;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api", name="api_extension_")
 */
class ExtensionController extends AbstractController
{
    private $logger;
    private $security;
    private $fleetUploadHandler;

    public function __construct(
        LoggerInterface $logger,
        Security $security,
        FleetUploadHandler $fleetUploadHandler
    ) {
        $this->logger = $logger;
        $this->security = $security;
        $this->fleetUploadHandler = $fleetUploadHandler;
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
}
