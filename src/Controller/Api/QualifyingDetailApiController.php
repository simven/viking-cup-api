<?php

namespace App\Controller\Api;

use App\Business\QualifyingDetailBusiness;
use App\Dto\QualifDetailDto;
use App\Entity\Qualifying;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/qualifyingDetail', name: 'api_qualifying_detail')]
class QualifyingDetailApiController extends AbstractController
{
    #[Route('/{qualifying}', name: 'update', methods: ['PUT'])]
    public function updateQualifyingDetail(
        QualifyingDetailBusiness $qualifyingDetailBusiness,
        Qualifying $qualifying,
        #[MapRequestPayload] QualifDetailDto $qualifDto
    ): Response
    {
        $qualifyingDetailBusiness->updateQualifyingDetail($qualifying, $qualifDto);

        return new Response();
    }
}