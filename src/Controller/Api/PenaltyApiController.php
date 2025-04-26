<?php

namespace App\Controller\Api;

use App\Business\PenaltyBusiness;
use App\Dto\PenaltyDto;
use App\Entity\Penalty;
use App\Entity\PilotRoundCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/penalties', name: 'api_penalty')]
class PenaltyApiController extends AbstractController
{
    #[Route('/{pilotRoundCategory}', name: 'list', methods: ['GET'])]
    public function getPenalties(
        PenaltyBusiness $penaltyBusiness,
        PilotRoundCategory $pilotRoundCategory
    ): Response
    {
        $penalties = $penaltyBusiness->getPenalties($pilotRoundCategory);

        return $this->json($penalties, Response::HTTP_OK, [], ['groups' => ['penalty', 'penaltyPenaltyReason', 'penaltyReason']]);
    }

    #[Route('/{pilotRoundCategory}', name: 'update', methods: ['PUT'])]
    public function updatePenalties(
        PenaltyBusiness $penaltyBusiness,
        PilotRoundCategory $pilotRoundCategory,
        #[MapRequestPayload(type: PenaltyDto::class)] array $penaltiesDto
    ): Response
    {
        $penalties = $penaltyBusiness->updatePenalties($pilotRoundCategory, $penaltiesDto);

        return $this->json($penalties, Response::HTTP_OK, [], ['groups' => ['penalty', 'penaltyPenaltyReason', 'penaltyReason']]);
    }

    #[Route('/{penalty}', name: 'delete', methods: ['DELETE'])]
    public function deletePenalty(
        PenaltyBusiness $penaltyBusiness,
        Penalty $penalty
    ): Response
    {
        $penaltyBusiness->deletePenalty($penalty);

        return new Response();
    }
}