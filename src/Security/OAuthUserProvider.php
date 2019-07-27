<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseProvider;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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

    private function persistOAuthInfos(PathUserResponse $response): void
    {
        $user = $this->userRepository->getByDiscordId($response->getUsername());

        if ($user !== null) {
            $user->setDiscordId($response->getUsername());
            $user->setDiscordTag($response->getData()[$response->getPath('discordtag')] ?? null);
            $user->setUsername($response->getNickname());
            $user->setNickname($response->getNickname());
            if (!$user->getApiToken()) {
                $user->setApiToken(User::generateToken());
            }
        } else {
            $this->registerNewUser($response->getUsername(), $response->getData()[$response->getPath('discordtag')] ?? null, $response->getNickname());
        }
        $this->entityManager->flush();
    }

    public function loadUserByUsername($discordId)
    {
        $user = $this->userRepository->getByDiscordId($discordId);
        if ($user === null) {
            throw new UsernameNotFoundException('OAuth user not found.');
        }

        return $user;
    }

    private function registerNewUser(string $discordId, ?string $discordTag, string $username): User
    {
        $newUser = new User(Uuid::uuid4());
        $newUser->setDiscordId($discordId);
        $newUser->setDiscordTag($discordTag);
        $newUser->setUsername($username);
        $newUser->setNickname($username);
        $newUser->setToken(User::generateToken());
        $newUser->setApiToken(User::generateToken());

        $this->entityManager->persist($newUser);

        return $newUser;
    }

    public function supportsClass($class): bool
    {
        return User::class === $class;
    }
}
