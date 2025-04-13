<?php

namespace App\Controller\Api;

use App\Business\QualifyingBusiness;
use App\Dto\QualifDto;
use App\Repository\CategoryRepository;
use App\Repository\RoundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/qualifying', name: 'api_qualifying')]
class QualifyingApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getRoundCategoryPilotsQualifying(
        QualifyingBusiness $qualifyingBusiness,
        RoundRepository $roundRepository,
        CategoryRepository $categoryRepository,
        #[MapQueryParameter] int $round,
        #[MapQueryParameter] int $category,
        #[MapQueryParameter] ?string $pilot = null
    ): Response
    {
        $round = $roundRepository->find($round);
        $category = $categoryRepository->find($category);

        $roundCategoryPilotsQualifying = $qualifyingBusiness->getRoundCategoryPilotsQualifying($round, $category, $pilot);

        return $this->json($roundCategoryPilotsQualifying);
    }

    #[Route('/ranking', name: 'qualifying_ranking', methods: ['GET'])]
    public function getQualifyingRanking(
        QualifyingBusiness $qualifyingBusiness,
        RoundRepository $roundRepository,
        CategoryRepository $categoryRepository,
        #[MapQueryParameter] int $round,
        #[MapQueryParameter] int $category
    ): Response
    {
        $round = $roundRepository->find($round);
        $category = $categoryRepository->find($category);

        $qualifyingRanking = $qualifyingBusiness->getQualifyingRanking($round, $category);

        return $this->json($qualifyingRanking, 200, [], ['groups' => ['pilot', 'pilotEvent', 'round', 'category']]);
    }

    #[Route('', name: 'update', methods: ['PUT'])]
    public function updateQualifying(
        QualifyingBusiness $qualifyingBusiness,
        #[MapRequestPayload] QualifDto $qualifDto
    ): Response
    {
        $qualifyingBusiness->updateQualifying($qualifDto);

        return new Response();
    }
}