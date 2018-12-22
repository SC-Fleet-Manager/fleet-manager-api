<?php

namespace App\Infrastructure\Controller;

use App\Domain\Exception\FleetUploadedTooCloseException;
use App\Domain\Exception\NotFoundHandleSCException;
use App\Domain\FleetUploadHandlerInterface;
use App\Domain\HandleSC;
use App\Domain\OrganisationFleetGeneratorInterface;
use App\Domain\SpectrumIdentification;
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
use Symfony\Component\Translation\TranslatorInterface;

class ApiController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * @Route("/upload", name="upload", methods={"POST"})
     *
     * Upload star citizen fleet for one user.
     */
    public function upload(
        Request $request,
        FormFactoryInterface $formFactory,
        FleetUploadHandlerInterface $fleetUploadHandler): Response
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

        $fleetData = \json_decode(file_get_contents($fleetUpload->fleetFile->getRealPath()), true);

        try {
            $fleetUploadHandler->handle(new HandleSC($fleetUpload->handleSC), $fleetData);
        } catch (FleetUploadedTooCloseException $e) {
            return $this->json([
                'error' => 'uploaded_too_close',
                'errorMessage' => 'Your fleet has been uploaded recently. Please wait before re-uploading.',
            ], 400);
        } catch (NotFoundHandleSCException $e) {
            return $this->json([
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The handle SC %s does not exist.', $fleetUpload->handleSC),
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
        if (\strlen($organisation) !== 3) {
            return $this->json([
                'error' => 'invalid_param',
                'param' => 'organisation',
                'errorMessage' => 'The organisation parameter must be 3 characters long.',
            ], 400);
        }
        $file = $fleetGenerator->generateFleetFile(new SpectrumIdentification($organisation));
        $filename = 'organisation_fleet.json';

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'application/json');
        $response->deleteFileAfterSend();
        $response::trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $filename,
            $filename
        );

        return $response;
    }
}
