<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LostPasswordCreateController extends AbstractController
{
    /**
     * @Route("/lost-password-create", name="security_lost_password_create", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        return $this->json(null, 204);
    }
}
