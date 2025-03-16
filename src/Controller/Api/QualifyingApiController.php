<?php

namespace App\Controller\Api;

use App\Business\QualifyingBusiness;
use App\Dto\RoundCategoryPilotsQualifyingDto;
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

    #[Route('', name: 'update', methods: ['PUT'])]
    public function updateRoundCategoryPilotsQualifying(
        QualifyingBusiness $qualifyingBusiness,
        #[MapRequestPayload(type: RoundCategoryPilotsQualifyingDto::class)] array $roundCategoryPilotsQualifyingDto
    ): Response
    {
        $qualifyingBusiness->updateRoundCategoryPilotsQualifying($roundCategoryPilotsQualifyingDto);

        return new Response();
    }
}