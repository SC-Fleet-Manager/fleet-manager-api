<?php

namespace App\Security;

use Algatux\InfluxDbBundle\Events\DeferredUdpEvent;
use App\Entity\Funding;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Exception\AlreadyLinkedDiscordException;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseProvider;
use InfluxDB\Database;
use InfluxDB\Point;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthUserProvider extends BaseProvider implements AccountConnectorInterface
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private RequestStack $requestStack;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
    }

    public function connect(UserInterface $user, UserResponseInterface $response): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', User::class, get_class($user)));
        }

        /** @var PathUserResponse $response */
        $userAlreadyLinked = $this->userRepository->getByDiscordId($response->getUsername());

        $this->entityManager->beginTransaction();
        try {
            if ($userAlreadyLinked !== null && $userAlreadyLinked->getId() !== $user->getId()) {
                if ($user->getCitizen() !== null && $userAlreadyLinked->getCitizen() !== null) {
                    // citizen conflict

                    $user->setPendingDiscordId($response->getUsername());
                    $this->entityManager->flush();
                    $this->entityManager->getConnection()->commit();

                    throw new AlreadyLinkedDiscordException($user, $userAlreadyLinked);
                }

                $newCitizen = $user->getCitizen();
                if ($userAlreadyLinked->getCitizen() !== null) {
                    $newCitizen = $userAlreadyLinked->getCitizen();
                    $userAlreadyLinked->setCitizen(null);
                }
                if ($userAlreadyLinked->getCreatedAt() < $user->getCreatedAt()) {
                    $user->setCreatedAt(clone $userAlreadyLinked->getCreatedAt());
                }
                $fundings = $this->entityManager->getRepository(Funding::class)->findBy(['user' => $userAlreadyLinked]);
                foreach ($fundings as $funding) {
                    $this->entityManager->remove($funding);
                }
                $this->entityManager->remove($userAlreadyLinked);
                $this->entityManager->flush();
                $user->setCitizen($newCitizen);
            }

            $user->setDiscordId($response->getUsername());
            $user->setDiscordTag($response->getData()[$response->getPath('discordtag')] ?? null);
            $user->setUsername($response->getNickname());
            $user->setNickname($response->getNickname());
            if (!$user->getApiToken()) {
                $user->setApiToken(User::generateToken());
            }
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (AlreadyLinkedDiscordException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }
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

    public function refreshUser(UserInterface $user)
    {
        /** @var User $user */
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        if ($user->getDiscordId() === null) {
            throw new UnsupportedUserException(sprintf('The user %s does not have a Discord ID.', $user->getId()));
        }

        return $this->loadUserByUsername($user->getDiscordId());
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

        $this->eventDispatcher->dispatch(new DeferredUdpEvent([new Point(
            'app.registration',
            1,
            ['method' => 'discord', 'host' => $this->requestStack->getCurrentRequest()->getHost()],
        )], Database::PRECISION_SECONDS), DeferredUdpEvent::NAME);

        return $newUser;
    }

    public function supportsClass($class): bool
    {
        return User::class === $class;
    }
}
