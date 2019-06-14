<?php

namespace App\Command;

use App\Entity\Citizen;
use App\Repository\CitizenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateLastFleetCommand extends Command
{
    private $citizenRepository;
    private $entityManager;

    public function __construct(CitizenRepository $citizenRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->citizenRepository = $citizenRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setName('app:migrate-last-fleet');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Citizen[] $citizens */
        $citizens = $this->citizenRepository->findBy(['lastFleet' => null]);
        $output->writeln(sprintf('Migrating %d last fleets...', count($citizens)));

        foreach ($citizens as $citizen) {
            $lastFleet = null;
            foreach ($citizen->getFleets() as $fleet) {
                if ($lastFleet === null || $fleet->getVersion() > $lastFleet->getVersion()) {
                    $lastFleet = $fleet;
                }
            }
            $citizen->setLastFleet($lastFleet);
        }

        $this->entityManager->flush();

        $io->success('Last fleets migrated successfully.');

        return 0;
    }
}
