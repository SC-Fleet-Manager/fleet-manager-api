<?php

namespace App\Security;

use HWI\Bundle\OAuthBundle\Security\Http\EntryPoint\OAuthEntryPoint as BaseEntryPoint;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuthEntryPoint extends BaseEntryPoint
{
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse([
            'error' => 'no_auth',
            'loginUrl' => $this->httpUtils->generateUri($request, $this->loginPath),
        ], 401);
    }
}
