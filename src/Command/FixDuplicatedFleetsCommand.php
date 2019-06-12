<?php

namespace App\Command;

use App\Entity\Citizen;
use App\Entity\Fleet;
use App\Repository\CitizenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixDuplicatedFleetsCommand extends Command
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
        $this->setName('app:fix-duplicated-fleets')
            ->addArgument('citizenHandle', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null !== $citizenHandle = $input->getArgument('citizenHandle')) {
            $citizens = [$this->citizenRepository->findOneBy(['actualHandle' => $citizenHandle])];
        } else {
            /** @var Citizen[] $citizens */
            $citizens = $this->citizenRepository->findAll();
        }

        $fixes = 0;
        foreach ($citizens as $citizen) {
            /** @var Fleet[] $fleets */
            $fleets = iterator_to_array($citizen->getFleets());
            if (empty($fleets)) {
                continue;
            }
            usort($fleets, static function (Fleet $fleet1, Fleet $fleet2) {
                return $fleet1->getVersion() - $fleet2->getVersion();
            });
            for ($i = 0; $i < count($fleets) - 1; ++$i) {
                if (!$this->hasDiff($fleets[$i], $fleets[$i + 1])) {
                    $citizen->removeFleet($fleets[$i + 1]);
                    $this->entityManager->remove($fleets[$i + 1]);
                    ++$fixes;
                }
            }
            $fleets = iterator_to_array($citizen->getFleets());
            usort($fleets, static function (Fleet $fleet1, Fleet $fleet2) {
                return $fleet1->getVersion() - $fleet2->getVersion();
            });
            $version = 1;
            foreach ($fleets as $fleet) {
                $fleet->setVersion($version++);
            }
            $citizen->setLastFleet(end($fleets));
        }

        $this->entityManager->flush();

        $io->writeln(sprintf('%d fleets deleted.', $fixes));
        $io->success('Fleets deduplicated successfully.');

        return 0;
    }

    private function hasDiff(Fleet $newFleet, Fleet $lastFleet): bool
    {
        if (count($newFleet->getShips()) !== count($lastFleet->getShips())) {
            return true;
        }
        $countSameShips = 0;
        foreach ($newFleet->getShips() as $newShip) {
            foreach ($lastFleet->getShips() as $lastShip) {
                if ($newShip->equals($lastShip)) {
                    ++$countSameShips;
                    break;
                }
            }
        }

        return $countSameShips !== count($newFleet->getShips());
    }
}
