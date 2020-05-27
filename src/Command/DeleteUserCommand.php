<?php

namespace App\Command;

use App\Event\CitizenDeletedEvent;
use App\Repository\FundingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DeleteUserCommand extends Command
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private FundingRepository $fundingRepository;

    public function __construct(
        UserRepository $userRepository,
        FundingRepository $fundingRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->fundingRepository = $fundingRepository;
    }

    protected function configure(): void
    {
        $this->setName('app:delete-user')
            ->addArgument('userId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('userId');
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new \RuntimeException('User does not exist.');
        }

        $citizen = $user->getCitizen();
        if ($citizen !== null) {
            $this->entityManager->remove($citizen);
            // TODO : if transaction rollbacks, the event is inconsistent : set citizen + its organizations in Event and dispatch it after commit()
            $this->eventDispatcher->dispatch(new CitizenDeletedEvent($citizen));
        }
        $fundings = $this->fundingRepository->findBy(['user' => $user]);
        foreach ($fundings as $funding) {
            $funding->setUser(null);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $output->writeln('<info>User deleted successfully.</info>');

        return 0;
    }
}
