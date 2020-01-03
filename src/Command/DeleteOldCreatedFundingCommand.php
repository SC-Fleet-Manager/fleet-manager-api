<?php

namespace App\Command;

use App\Repository\FundingRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteOldCreatedFundingCommand extends Command
{
    private FundingRepository $fundingRepository;
    private SymfonyStyle $io;

    public function __construct(FundingRepository $fundingRepository)
    {
        parent::__construct();
        $this->fundingRepository = $fundingRepository;
    }

    protected function configure(): void
    {
        $this->setName('app:delete-old-created-funding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $answer = $this->io->askQuestion(new ConfirmationQuestion('Would you really want to delete old created fundings?', false));
        if ($answer) {
            $this->fundingRepository->deleteOldCreated(new \DateTimeImmutable('-1 week'));
        }

        return 0;
    }
}
