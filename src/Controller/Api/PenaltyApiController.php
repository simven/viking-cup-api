<?php

namespace App\Controller\Api;

use App\Business\PenaltyBusiness;
use App\Dto\PenaltyDto;
use App\Entity\PilotRoundCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/penalties', name: 'api_penalty')]
class PenaltyApiController extends AbstractController
{
    #[Route('/{pilotRoundCategory}', name: 'update', methods: ['POST'])]
    public function updatePenalties(
        PenaltyBusiness $penaltyBusiness,
        PilotRoundCategory $pilotRoundCategory,
        #[MapRequestPayload(type: PenaltyDto::class)] array $penaltiesDto,
    ): Response
    {
        $penaltyBusiness->updatePenalties($pilotRoundCategory, $penaltiesDto);

        return new Response();
    }
}