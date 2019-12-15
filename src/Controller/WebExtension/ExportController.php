<?php

namespace App\Controller\WebExtension;

use App\Entity\User;
use App\Exception\BadCitizenException;
use App\Exception\FleetUploadedTooCloseException;
use App\Exception\InvalidFleetDataException;
use App\Exception\NotFoundHandleSCException;
use App\Service\Citizen\Fleet\FleetUploadHandler;
use App\Service\WebExtension\WebExtensionVersionComparator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ExportController extends AbstractController
{
    private Security $security;
    private FleetUploadHandler $fleetUploadHandler;
    private LoggerInterface $logger;
    private WebExtensionVersionComparator $webExtVersionComparator;

    public function __construct(
        Security $security,
        FleetUploadHandler $fleetUploadHandler,
        LoggerInterface $webExtensionLogger,
        WebExtensionVersionComparator $webExtVersionComparator
    ) {
        $this->security = $security;
        $this->fleetUploadHandler = $fleetUploadHandler;
        $this->logger = $webExtensionLogger;
        $this->webExtVersionComparator = $webExtVersionComparator;
    }

    /**
     * @Route("/api/export", name="web_extension_export", methods={"POST","OPTIONS"})
     */
    public function __invoke(Request $request): Response
    {
        if ($request->isMethod('OPTIONS')) {
            // Preflight CORS request.
            return new JsonResponse(null, 204, [
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-FME-Version',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            ]);
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $extensionVersion = $request->headers->get('X-FME-Version');
        $webExtVersionComparison = $this->webExtVersionComparator->compareVersions($extensionVersion);

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
        $fleetData = json_decode($contents, true);

        if (JSON_ERROR_NONE !== $jsonError = json_last_error()) {
            $this->logger->error('Failed to decode json from fleet file', ['json_error' => $jsonError, 'fleet_file_contents' => $contents]);

            return $this->json([
                'error' => 'bad_json',
                'errorMessage' => 'Your fleet file is not JSON well formatted. Please check it.',
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

        $status = 204;
        $responsePayload = null;
        try {
            $this->fleetUploadHandler->handle($citizen, $fleetData);
        } catch (FleetUploadedTooCloseException $e) {
            $status = 400;
            $responsePayload = [
                'error' => 'uploaded_too_close',
                'errorMessage' => 'Your fleet has been uploaded recently. Please wait before re-uploading.',
            ];
        } catch (NotFoundHandleSCException $e) {
            $status = 400;
            $responsePayload = [
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The SC handle %s does not exist.', $citizen->getActualHandle()),
                'context' => ['handle' => $citizen->getActualHandle()],
            ];
        } catch (BadCitizenException $e) {
            $status = 400;
            $responsePayload = [
                'error' => 'bad_citizen',
                'errorMessage' => sprintf('Your SC handle has probably changed. Please update it in <a href="/profile/">your Profile</a>.'),
            ];
        } catch (InvalidFleetDataException $e) {
            $status = 400;
            $responsePayload = [
                'error' => 'invalid_fleet_data',
                'errorMessage' => sprintf('The fleet data in your file is invalid. Please check it.'),
            ];
        } catch (\Exception $e) {
            $this->logger->error('cannot handle fleet file', ['exception' => $e]);

            $status = 400;
            $responsePayload = [
                'error' => 'cannot_handle_file',
                'errorMessage' => 'Cannot handle the fleet file. Try again !',
            ];
        }

        if ($webExtVersionComparison !== null) {
            $status = 200;
            $responsePayload = $responsePayload ?? [];
            $responsePayload['lastVersion'] = $webExtVersionComparison->lastVersion;
            $responsePayload['requestExtensionVersion'] = $webExtVersionComparison->requestExtensionVersion;
            $responsePayload['needUpgradeVersion'] = true;// $webExtVersionComparison->needUpgradeVersion;
        }

        return $this->json($responsePayload, $status);
    }
}
