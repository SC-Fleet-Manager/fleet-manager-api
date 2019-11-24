<?php

namespace App\Controller\Organization\Fleet;

use App\Entity\User;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Organization\Fleet\OrganizationFleetExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class ExportFleetController extends AbstractController
{
    private $security;
    private $fleetOrganizationGuard;
    private $organizationFleetExporter;
    private $serializer;

    public function __construct(Security $security, FleetOrganizationGuard $fleetOrganizationGuard, OrganizationFleetExporter $organizationFleetExporter, SerializerInterface $serializer)
    {
        $this->security = $security;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->organizationFleetExporter = $organizationFleetExporter;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/export-orga-fleet/{organizationSid}", name="orga_fleet_export_fleet", methods={"GET"})
     */
    public function __invoke(string $organizationSid): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        // If viewer is not in this orga, he doesn't see the users
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return new JsonResponse([
                'error' => 'no_citizen_created',
            ], 400);
        }
        if (!$citizen->hasOrganization($organizationSid)) {
            return new JsonResponse([
                'error' => 'not_in_orga',
            ], 404);
        }

        $data = $this->organizationFleetExporter->exportOrgaFleet($organizationSid);

        $csv = $this->serializer->serialize($data, 'csv');
        $filepath = sys_get_temp_dir().'/'.uniqid('', true);
        file_put_contents($filepath, $csv);

        $file = $this->file($filepath, 'export_'.$organizationSid.'.csv');
        $file->headers->set('Content-Type', 'application/csv');
        $file->deleteFileAfterSend();

        return $file;
    }
}
