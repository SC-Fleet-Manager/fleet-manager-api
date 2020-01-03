<?php

namespace App\Controller\MyFleet;

use App\Entity\User;
use App\Service\Citizen\Fleet\CitizenFleetGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CreateCitizenFleetFileController extends AbstractController
{
    private Security $security;
    private CitizenFleetGenerator $citizenFleetGenerator;

    public function __construct(Security $security, CitizenFleetGenerator $citizenFleetGenerator)
    {
        $this->security = $security;
        $this->citizenFleetGenerator = $citizenFleetGenerator;
    }

    /**
     * Combines the last version fleet of the given citizen.
     * Returns a downloadable json file.
     *
     * @Route("/api/create-citizen-fleet-file", name="my_fleet_create_citizen_fleet_file", methods={"GET"})
     */
    public function __invoke(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return new JsonResponse([
                'error' => 'no_citizen_created',
            ], 400);
        }

        try {
            $file = $this->citizenFleetGenerator->generateFleetFile($citizen->getNumber());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'file_not_generated',
            ], 400);
        }

        $fileResponse = $this->file($file, 'citizen_fleet.json');
        $fileResponse->headers->set('Content-Type', 'application/json');
        $fileResponse->deleteFileAfterSend();

        return $fileResponse;
    }
}
