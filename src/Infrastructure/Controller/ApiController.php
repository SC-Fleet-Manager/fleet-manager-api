<?php

namespace App\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/upload", name="upload")
     *
     * Upload star citizen fleet for one user.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function upload(Request $request): Response
    {


        return $this->json(null, 204);
    }
}
