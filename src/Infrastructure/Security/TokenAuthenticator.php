<?php

namespace App\Infrastructure\Security;

use App\Domain\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request)
    {
        return $request->headers->has('Authorization')
            && stripos($request->headers->get('Authorization'), 'Bearer') === 0;
    }

    public function getCredentials(Request $request)
    {
        return ['token' => trim(substr($request->headers->get('Authorization'), strlen('Bearer')))];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!$credentials['token']) {
            return null;
        }

        return $this->userRepository->getByToken($credentials['token']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true; // no credentials to check
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'error' => 'auth_fail',
            'message' => $exception->getMessage(),
        ], 403);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null; // continue
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null)
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
