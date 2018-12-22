<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Infrastructure\Security;

use App\Domain\User;
use App\Domain\UserRepositoryInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseProvider;
use Ramsey\Uuid\Uuid;

class OAuthUserProvider extends BaseProvider
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $citizen = $this->userRepository->getByUsername($username);
        dump($citizen);
        if ($citizen !== null) {
            return $citizen;
        }

        // register one
        return $this->registerNewUser($username);
    }

    private function registerNewUser(string $username): User
    {
        $newUser = new User(Uuid::uuid4(), $username);
        $newUser->createdAt = new \DateTimeImmutable();

        $this->userRepository->create($newUser);
dump($newUser);
        return $newUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        dump($class);
        return User::class === $class;
    }
}
