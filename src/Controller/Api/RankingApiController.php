<?php

namespace App\Controller\Api;

use App\Business\RankingBusiness;
use App\Repository\CategoryRepository;
use App\Repository\RoundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ranking', name: 'api_qualifying')]
class RankingApiController extends AbstractController
{
    #[Route('/global', name: 'global_ranking', methods: ['GET'])]
    public function getGlobalRanking(
        RankingBusiness $rankingBusiness,
        RoundRepository $roundRepository,
        CategoryRepository $categoryRepository,
        #[MapQueryParameter] int $round,
        #[MapQueryParameter] int $category
    ): Response
    {
        $round = $roundRepository->find($round);
        $category = $categoryRepository->find($category);

        $rankingPilotRoundCategory = $rankingBusiness->getGlobalRanking($round, $category);

        return $this->json($rankingPilotRoundCategory, 200, [], ['groups' => ['pilotRoundCategory', 'pilotRoundCategoryPilot', 'pilot']]);
    }
}