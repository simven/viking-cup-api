<?php

namespace App\Controller\Api;

use App\Business\RoundBusiness;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/rounds', name: 'api_rounds')]
class RoundApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getRounds(
        RoundBusiness $roundBusiness
    ): Response
    {
        $rounds = $roundBusiness->getRounds();

        return $this->json($rounds, 200, [], ['groups' => ['round', 'roundEvent', 'event']]);
    }
}