<?php

namespace App\Controller\Api;

use App\Business\PilotRoundCategoryBusiness;
use App\Dto\PilotRoundCategoryDto;
use App\Entity\PilotRoundCategory;
use App\Repository\CategoryRepository;
use App\Repository\RoundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/pilotRoundCategory', name: 'api_pilot_round_category')]
class PilotRoundCategoryApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getPilotRoundCategories(
        PilotRoundCategoryBusiness $pilotRoundCategoryBusiness,
        RoundRepository $roundRepository,
        CategoryRepository $categoryRepository,
        #[MapQueryParameter] string $round,
        #[MapQueryParameter] string $category,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $limit = 50,
        #[MapQueryParameter] ?string $sort = null,
        #[MapQueryParameter] ?string $order = null,
        #[MapQueryParameter] ?string $pilot = null
    ): Response
    {
        $round = $roundRepository->find($round);
        $category = $categoryRepository->find($category);

        $pilotRoundCategories = $pilotRoundCategoryBusiness->getPilotRoundCategoryPaginate($round, $category, $page, $limit, $sort, $order, $pilot);

        return $this->json($pilotRoundCategories, Response::HTTP_OK, [], ['groups' => ['pilotRoundCategory', 'pilotRoundCategoryPilot', 'pilot', 'pilotEvents', 'pilotEvent']]);
    }

    #[Route('/{pilotRoundCategory}', name: 'update', methods: ['PUT'])]
    public function update(
        PilotRoundCategoryBusiness $pilotRoundCategoryBusiness,
        PilotRoundCategory $pilotRoundCategory,
        #[MapRequestPayload] PilotRoundCategoryDto $pilotRoundCategoryDto
    ): Response
    {
        $pilotRoundCategoryBusiness->update($pilotRoundCategory, $pilotRoundCategoryDto);

        return new Response();
    }
}