<?php

namespace App\Controller\WebExtension;

use App\Entity\User;
use App\Exception\BadCitizenException;
use App\Exception\FleetUploadedTooCloseException;
use App\Exception\InvalidFleetDataException;
use App\Exception\NotFoundHandleSCException;
use App\Service\Citizen\Fleet\FleetUploadHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ExportController extends AbstractController
{
    private $security;
    private $fleetUploadHandler;
    private $logger;

    public function __construct(Security $security, FleetUploadHandler $fleetUploadHandler, LoggerInterface $webExtensionLogger)
    {
        $this->security = $security;
        $this->fleetUploadHandler = $fleetUploadHandler;
        $this->logger = $webExtensionLogger;
    }

    /**
     * @Route("/api/export", name="web_extension_export", methods={"POST","OPTIONS"})
     */
    public function __invoke(Request $request): Response
    {
        if ($request->isMethod('OPTIONS')) {
            // Preflight CORS request.
            return new JsonResponse(null, 204);
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $contents = $request->getContent();
        if (($contentSize = strlen($contents)) >= 2 * 1000 * 1000) {
            $errors = [sprintf('The data are too large (%.2f MB). Allowed maximum size is 2 MB.', $contentSize / (1000 * 1000))];
            $this->logger->warning('Upload fleet form error.', [
                'form_errors' => $errors,
            ]);

            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }
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
