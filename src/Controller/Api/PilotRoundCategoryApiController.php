<?php

namespace App\Controller\Api;

use App\Business\PilotRoundCategoryBusiness;
use App\Dto\PilotRoundCategoryDto;
use App\Entity\PilotRoundCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/pilotRoundCategory', name: 'api_pilot_round_category')]
class PilotRoundCategoryApiController extends AbstractController
{
    #[Route('/{pilotRoundCategory}', name: 'update', methods: ['PUT'])]
    public function update(
        PilotRoundCategoryBusiness $qualifyingBusiness,
        PilotRoundCategory $pilotRoundCategory,
        #[MapRequestPayload] PilotRoundCategoryDto $pilotRoundCategoryDto
    ): Response
    {
        $qualifyingBusiness->update($pilotRoundCategory, $pilotRoundCategoryDto);

        return new Response();
    }
}