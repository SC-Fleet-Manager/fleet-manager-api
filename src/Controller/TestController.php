<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TestController extends AbstractController
{
    public function login(string $userId, ParameterBagInterface $parameterBag, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager): Response
    {
        if ($parameterBag->get('kernel.environment') !== 'test') {
            throw $this->createNotFoundException('Route available only in test environment.');
        }

        $user = $entityManager->getRepository(User::class)->find($userId);

        $token = new OAuthToken(md5(mt_rand()), ['ROLE_USER']);
        $token->setResourceOwnerName('discord');
        $token->setUser($user);
        $token->setAuthenticated(true);
        $tokenStorage->setToken($token);

        return $this->redirectToRoute('dashboard_index');
    }
}
