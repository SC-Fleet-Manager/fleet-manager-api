<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseProvider;
use Ramsey\Uuid\Uuid;

class OAuthUserProvider extends BaseProvider
{
    private $userRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $this->persistOAuthInfos($response);

        return $this->loadUserByUsername($response->getUsername());
    }

    private function persistOAuthInfos(UserResponseInterface $response): void
    {
        $user = $this->userRepository->getByDiscordId($response->getUsername());
        if ($user === null) {
            $user = $this->userRepository->getByUsername($response->getNickname());
        }

        if ($user !== null) {
            $user->setDiscordId($response->getUsername());
            $user->setUsername($response->getNickname());
            if (!$user->getApiToken()) {
                $user->setApiToken(User::generateToken());
            }
        } else {
            $this->registerNewUser($response->getUsername(), $response->getNickname());
        }
        $this->entityManager->flush();
    }

    public function loadUserByUsername($discordId)
    {
        return $this->userRepository->getByDiscordId($discordId);
    }

    private function registerNewUser(string $discordId, string $username): User
    {
        $newUser = new User(Uuid::uuid4());
        $newUser->setDiscordId($discordId);
        $newUser->setUsername($username);
        $newUser->setToken(User::generateToken());
        $newUser->setApiToken(User::generateToken());

        $this->entityManager->persist($newUser);

        return $newUser;
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
