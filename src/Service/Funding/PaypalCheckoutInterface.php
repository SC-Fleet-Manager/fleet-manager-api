<?php

namespace App\Service\Funding;

use App\Entity\Funding;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

interface PaypalCheckoutInterface
{
    public const BACKING_REFID = '1a454096-31bc-47cb-9400-07b752809220';

    public function create(Funding $funding, User $user, string $locale): void;

    public function capture(Funding $funding): void;

    public function complete(Funding $funding): void;

    public function refund(Funding $funding): void;

    public function deny(Funding $funding): void;

    public function verifySignature(Request $request): bool;
}
