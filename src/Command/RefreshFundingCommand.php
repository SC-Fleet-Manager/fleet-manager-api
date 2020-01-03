<?php

namespace App\Command;

use App\Entity\Funding;
use App\Event\FundingUpdatedEvent;
use App\Repository\FundingRepository;
use App\Service\Funding\PaypalCheckout;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RefreshFundingCommand extends Command
{
    private PaypalCheckout $paypalCheckout;
    private FundingRepository $fundingRepository;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private SymfonyStyle $io;

    public function __construct(
        PaypalCheckout $paypalCheckout,
        FundingRepository $fundingRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
        $this->paypalCheckout = $paypalCheckout;
        $this->fundingRepository = $fundingRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure(): void
    {
        $this->setName('app:refresh-funding')
            ->addArgument('fundingIds', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        foreach ($input->getArgument('fundingIds') as $fundingId) {
            $this->refreshFunding($fundingId);
        }

        return 0;
    }

    private function refreshFunding(string $id): void
    {
        /** @var Funding $funding */
        $funding = $this->fundingRepository->find($id);
        if ($funding === null) {
            $this->io->error(sprintf('The funding %s does not exist.', $id));

            return;
        }

        $this->io->writeln(sprintf('Refreshing the funding %s that has the status: %s', $id, $funding->getPaypalStatus()));

        $this->paypalCheckout->refreshOrder($funding);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(new FundingUpdatedEvent($funding));

        $this->io->success('The funding has now the status: '.$funding->getPaypalStatus());
    }
}
