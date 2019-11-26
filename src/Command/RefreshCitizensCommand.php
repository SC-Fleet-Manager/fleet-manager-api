<?php

namespace App\Command;

use App\Entity\Citizen;
use App\Repository\CitizenRepository;
use App\Service\Citizen\CitizenRefresher;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RefreshCitizensCommand extends Command
{
    private $citizenInfosProvider;
    private $citizenRefresher;
    private $citizenRepository;
    private $entityManager;

    public function __construct(
        CitizenInfosProviderInterface $citizenInfosProvider,
        CitizenRefresher $citizenRefresher,
        CitizenRepository $citizenRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->citizenRefresher = $citizenRefresher;
        $this->citizenRepository = $citizenRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setName('app:refresh-citizens')
            ->addArgument('citizenHandle', InputArgument::OPTIONAL)
            ->addOption('all');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Citizen[] $citizens */
        $citizens = [];
        if ($input->getArgument('citizenHandle')) {
            $citizen = $this->citizenRepository->findOneBy(['actualHandle' => $input->getArgument('citizenHandle')]);
            if ($citizen === null) {
                $io->error('Citizen does not exist.');

                return 1;
            }
            $citizens[] = $citizen;
        } elseif ($input->getOption('all')) {
            $citizens = $this->citizenRepository->findAll();
        } else {
            $citizens = $this->citizenRepository->createQueryBuilder('c')
                ->where('c.lastRefresh < :datetimeLimit OR c.lastRefresh IS NULL')
                ->orderBy('c.lastRefresh', 'ASC')
                ->setMaxResults(100)
                ->setParameters([
                    'datetimeLimit' => new \DateTimeImmutable('-3 days'),
                ])
                ->getQuery()
                ->getResult();
        }

        $output->writeln(sprintf('Refreshing %d citizens...', count($citizens)));
        $cc = 1;
        $orgaSids = [];
        foreach ($citizens as $citizen) {
            $citizenInfos = $this->citizenInfosProvider->retrieveInfos($citizen->getActualHandle());
            foreach ($citizenInfos->organizations as $orgaInfo) {
                if (!in_array($orgaInfo->sid->getSid(), $orgaSids, true)) {
                    $orgaSids[] = $orgaInfo->sid->getSid();
                }
            }
            $this->citizenRefresher->refreshCitizen($citizen, $citizenInfos);

            if ($cc % 10 === 0) {
                $output->writeln(sprintf('%d citizens refresh.', $cc));
            }
            ++$cc;
        }
        $this->entityManager->flush();
        $io->success('Citizens refreshed successfully.');

        return 0;
    }
}
