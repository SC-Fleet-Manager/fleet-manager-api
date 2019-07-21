<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use App\Service\Exporter\OrganizationFleetExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class ExportMembersController extends AbstractController
{
    private $security;
    private $organizationRepository;

    public function __construct(Security $security, OrganizationRepository $organizationRepository)
    {
        $this->security = $security;
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * @Route("/api/organization/export-orga-members/{organizationSid}", name="organization_export_members", methods={"GET"})
     */
    public function __invoke(string $organizationSid, OrganizationFleetExporter $orgaFleetExporter, SerializerInterface $serializer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }
        /** @var Organization|null $organization */
        $organization = $this->organizationRepository->findOneBy(['organizationSid' => $organizationSid]);
        if ($organization === null) {
            return $this->json([
                'error' => 'not_found_orga',
                'errorMessage' => sprintf('The organization "%s" does not exist.', $organizationSid),
            ], 404);
        }

        if (!$this->isGranted('IS_ADMIN_MANAGEABLE', new SpectrumIdentification($organizationSid))) {
            return $this->json([
                'error' => 'not_enough_rights',
                'errorMessage' => sprintf('You must be an admin of %s to view these stats. Try to refresh your RSI profile in your <a href="/profile">profile page</a>.', $organization->getName()),
            ], 403);
        }

        try {
            $data = $orgaFleetExporter->exportOrgaMembers($organizationSid);
        } catch (\LogicException $e) {
            return $this->json([
                'error' => 'orga_too_big',
                'errorMessage' => 'Sorry, your orga is too big to retrieve the members list right now. We\'re currently searching a solution for this issue.',
            ], 400);
        }

        $csv = $serializer->serialize($data, 'csv');
        $filepath = sys_get_temp_dir().'/'.uniqid('', true);
        file_put_contents($filepath, $csv);

        $file = $this->file($filepath, 'export_'.$organizationSid.'.csv');
        $file->headers->set('Content-Type', 'application/csv');
        $file->deleteFileAfterSend();

        return $file;
    }
}
