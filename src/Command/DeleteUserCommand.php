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
    protected static $defaultName = 'app:delete-user';

    public function __construct(
        private UserRepository $userRepository,
        private FundingRepository $fundingRepository,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('userId', InputArgument::REQUIRED);
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
