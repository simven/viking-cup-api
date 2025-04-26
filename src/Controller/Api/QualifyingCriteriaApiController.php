<?php

namespace App\Controller\Api;

use App\Business\QualifyingBusiness;
use App\Business\QualifyingCriteriaBusiness;
use App\Dto\QualifDetailDto;
use App\Repository\CategoryRepository;
use App\Repository\PilotRoundCategoryRepository;
use App\Repository\RoundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/qualifyingCriteria', name: 'api_qualifying_criteria')]
class QualifyingCriteriaApiController extends AbstractController
{
    #[Route('', name: 'qualifying_criteria', methods: ['GET'])]
    public function getQualifyingCriteria(
        QualifyingCriteriaBusiness $qualifyingCriteriaBusiness,
    ): Response
    {
        $roundCategoryPilotsQualifying = $qualifyingCriteriaBusiness->getQualifyingCriteria();

        return $this->json($roundCategoryPilotsQualifying, Response::HTTP_OK, [], ['groups' => ['qualifyingCriteria']]);
    }
}