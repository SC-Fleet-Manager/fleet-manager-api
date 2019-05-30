<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SpaController extends AbstractController
{
    public function index(string $spaPath): Response
    {
        return $this->render('base.html.twig');
    }

    public function home(): Response
    {
        return $this->render('home.html.twig');
    }
}
