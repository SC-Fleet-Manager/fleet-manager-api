<?php

namespace App\Infrastructure\Controller;

use App\Domain\FleetUploadHandlerInterface;
use App\Domain\HandleSC;
use App\Infrastructure\Form\Dto\FleetUpload;
use App\Infrastructure\Form\FleetUploadForm;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/upload", name="upload")
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
                'error' => ['No data has been submitted.'],
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
                'error' => 'Invalid form.',
                'formErrors' => $errors,
            ], 400);
        }

        $fleetData = \json_decode(file_get_contents($fleetUpload->fleetFile->getRealPath()), true);

        try {
            $fleetUploadHandler->handle(new HandleSC($fleetUpload->handleSC), $fleetData);
        } catch (\Exception $e) {
            $this->logger->error('exception raised', ['exception' => $e]);
            return $this->json([
                'error' => 'Cannot handle the fleet file. Try again !',
            ], 400);
        }

        return $this->json(null, 204);
    }
}
