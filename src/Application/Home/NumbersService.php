<?php

namespace App\Application\Home;

use App\Application\Home\Output\NumbersOutput;
use App\Application\Repository\UserRepositoryInterface;

class NumbersService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(): NumbersOutput
    {
        return new NumbersOutput($this->userRepository->countUsers());
    }
}
