<?php

namespace App\Infrastructure\Security;

use App\Domain\User;
use App\Domain\UserRepositoryInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseProvider;
use Ramsey\Uuid\Uuid;

class OAuthUserProvider extends BaseProvider
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
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
            $user->discordId = $response->getUsername();
            $user->username = $response->getNickname();
            if (!$user->apiToken) {
                $user->apiToken = User::generateToken();
            }
            $this->userRepository->update($user);
        } else {
            $this->registerNewUser($response->getUsername(), $response->getNickname());
        }
    }

    public function loadUserByUsername($discordId)
    {
        return $this->userRepository->getByDiscordId($discordId);
    }

    private function registerNewUser(string $discordId, string $username): User
    {
        $newUser = new User(Uuid::uuid4(), $discordId);
        $newUser->username = $username;
        $newUser->createdAt = new \DateTimeImmutable();
        $newUser->token = User::generateToken();
        $newUser->apiToken = User::generateToken();

        $this->userRepository->create($newUser);

        return $newUser;
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
