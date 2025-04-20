<?php

namespace App\Controller\Api;

use App\Business\PenaltyReasonBusiness;
use App\Dto\PenaltyReasonDto;
use App\Entity\PenaltyReason;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/penaltyReasons', name: 'api_penalty_reason')]
class PenaltyReasonApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getPenaltyReasons(
        PenaltyReasonBusiness $penaltyReasonBusiness
    ): Response
    {
        $penaltyReasons = $penaltyReasonBusiness->getPenaltyReasons();

        return $this->json($penaltyReasons, 200, [], ['groups' => ['penaltyReason']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createPenaltyReason(
        PenaltyReasonBusiness $penaltyReasonBusiness,
        #[MapRequestPayload] PenaltyReasonDto $penaltyReasonDto
    ): Response
    {
        $penaltyReason = $penaltyReasonBusiness->createPenaltyReason($penaltyReasonDto);

        return $this->json($penaltyReason, 200, [], ['groups' => ['penaltyReason']]);
    }

    #[Route('/{penaltyReason}', name: 'delete', methods: ['DELETE'])]
    public function deletePenaltyReason(
        PenaltyReasonBusiness $penaltyReasonBusiness,
        PenaltyReason $penaltyReason
    ): Response
    {
        $penaltyReasonBusiness->deletePenaltyReason($penaltyReason);

        return new Response();
    }
}