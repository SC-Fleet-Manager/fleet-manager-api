<?php

namespace App\Command;

use App\Entity\CitizenOrganization;
use App\Repository\CitizenOrganizationRepository;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateOrganizationCommand extends Command
{
    private $citizenOrgaRepository;
    private $orgaRepository;
    private $entityManager;

    public function __construct(
        CitizenOrganizationRepository $citizenOrgaRepository,
        OrganizationRepository $orgaRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->citizenOrgaRepository = $citizenOrgaRepository;
        $this->orgaRepository = $orgaRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setName('app:migrate-organization');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var CitizenOrganization[] $citizenOrgas */
        $citizenOrgas = $this->citizenOrgaRepository->findBy(['organization' => null]);
        $output->writeln(sprintf('Migrating %d organizations...', count($citizenOrgas)));

        foreach ($citizenOrgas as $citizenOrga) {
            $orga = $this->orgaRepository->findOneBy(['organizationSid' => $citizenOrga->getOrganizationSid()]);
            $citizenOrga->setOrganization($orga);
        }

        $this->entityManager->flush();

        $io->success('Organizations migrated successfully.');

        return 0;
    }
}
