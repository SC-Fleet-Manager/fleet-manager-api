<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Security\OAuthUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConnectController extends AbstractController
{
    /** @see user fixtures */
    private const DEFAULT_USER_ID = 'd92e229e-e743-4583-905a-e02c57eacfe0';

    private $tokenStorage;
    private $entityManager;
    private $accountConnector;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, OAuthUserProvider $accountConnector)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->accountConnector = $accountConnector;
    }

    public function login(Request $request, string $service): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($request->get('userId', self::DEFAULT_USER_ID));

        $token = new OAuthToken(md5(mt_rand()), $user->getRoles());
        $token->setResourceOwnerName($service);
        $token->setUser($user);
        $token->setAuthenticated(true);
        $this->tokenStorage->setToken($token);

        return $this->redirect('/profile');
    }

    public function connectService(Request $request, $service): Response
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedException(sprintf('You must be logged with a %s.', User::class));
        }

        $userInformation = new PathUserResponse();
        $userInformation->setPaths([
            'identifier' => 'identifier',
            'discordtag' => 'discordtag',
            'nickname' => 'nickname',
        ]);
        $userInformation->setData([
            'identifier' => $request->get('discordId', '123456789'),
            'discordtag' => $request->get('discordTag', '9876'),
            'nickname' => $request->get('nickname', 'foobar'),
        ]);
        $this->accountConnector->connect($currentUser, $userInformation);

        $token = new OAuthToken(md5(mt_rand()), $currentUser->getRoles());
        $token->setResourceOwnerName($service);
        $token->setUser($currentUser);
        $token->setAuthenticated(true);
        $this->tokenStorage->setToken($token);

        return $this->redirect('/profile');
    }
}
