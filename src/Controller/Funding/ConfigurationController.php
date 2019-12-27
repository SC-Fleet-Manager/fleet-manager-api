<?php

namespace App\Controller\Funding;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    private string $currency;
    private string $paypalClientId;

    public function __construct(string $currency, string $paypalClientId)
    {
        $this->currency = $currency;
        $this->paypalClientId = $paypalClientId;
    }

    /**
     * @Route("/api/funding/configuration", name="funding_ladder_configuration", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        return $this->json([
            'currency' => $this->currency,
            'paypalClientId' => $this->paypalClientId,
        ]);
    }
}
