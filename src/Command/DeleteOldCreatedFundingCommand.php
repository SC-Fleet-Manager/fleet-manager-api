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
    public function __construct(private FundingRepository $fundingRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:delete-old-created-funding');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $answer = $io->askQuestion(new ConfirmationQuestion('Would you really want to delete old created fundings?', false));
        if ($answer) {
            $this->fundingRepository->deleteOldCreated(new \DateTimeImmutable('-1 week'));
        }

        return 0;
    }
}
