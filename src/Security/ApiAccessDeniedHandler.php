<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;

class ApiAccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private $httpUtils;

    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        return new JsonResponse([
            'error' => 'forbidden',
            'loginUrl' => $this->httpUtils->generateUri($request, '/login'),
        ], 403);
    }
}
