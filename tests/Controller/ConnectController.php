<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ConnectController extends AbstractController
{
    /** @see user fixtures */
    private const DEFAULT_USER_ID = 'd92e229e-e743-4583-905a-e02c57eacfe0';

    private $tokenStorage;
    private $entityManager;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    public function login(Request $request, string $service): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($request->get('userId', self::DEFAULT_USER_ID));

        $token = new OAuthToken(md5(mt_rand()), $user->getRoles());
        $token->setResourceOwnerName($service);
        $token->setUser($user);
        $token->setAuthenticated(true);
        $this->tokenStorage->setToken($token);

        return $this->redirect('/#/profile');
    }
}
