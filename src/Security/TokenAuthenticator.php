<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization')
            && stripos($request->headers->get('Authorization'), 'Bearer') === 0;
    }

    public function getCredentials(Request $request): array
    {
        return ['token' => trim(substr($request->headers->get('Authorization'), strlen('Bearer')))];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if (!$credentials['token']) {
            return null;
        }

        return $this->userRepository->getByToken($credentials['token']);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true; // no credentials to check
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => 'auth_fail',
            'message' => $exception->getMessage(),
        ], 403);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null; // continue
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return new JsonResponse(null, 204, [
                'Access-Control-Allow-Headers' => 'Authorization,Content-Type',
                'Access-Control-Allow-Methods' => 'POST',
            ]);
        }

        return new JsonResponse([
            'error' => 'auth_needed',
            'message' => 'Authentication Required',
        ], 401);
    }
}
