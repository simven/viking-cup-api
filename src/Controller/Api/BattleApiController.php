<?php

namespace App\Controller\Api;

use App\Business\BattleBusiness;
use App\Repository\CategoryRepository;
use App\Repository\RoundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/battles', name: 'api_battles')]
class BattleApiController extends AbstractController
{
    #[Route('/init', name: 'init_battle_versus', methods: ['POST'])]
    public function initBattleVersus(
        BattleBusiness $battleBusiness,
        RoundRepository $roundRepository,
        CategoryRepository $categoryRepository,
        #[MapQueryParameter] int $round,
        #[MapQueryParameter] int $category
    ): Response
    {
        $round = $roundRepository->find($round);
        $category = $categoryRepository->find($category);

        $battleBusiness->initBattleVersus($round, $category);

        return new Response();
    }

    #[Route('/generate/{passage}', name: 'generate_battle_versus', methods: ['POST'])]
    public function generateBattleVersus(
        BattleBusiness $battleBusiness,
        int $passage
    ): Response
    {
        $battleBusiness->generateNextRound($passage);

        return new Response();
    }
}