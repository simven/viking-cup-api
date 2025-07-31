<?php

namespace App\Controller\Api;

use App\Business\CommissaireBusiness;
use App\Dto\CommissaireDto;
use App\Dto\CreateCommissaireDto;
use App\Entity\Commissaire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/commissaires', name: 'api_commissaires')]
class CommissaireApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getCommissaires(
        CommissaireBusiness $commissaireBusiness,
        #[MapQueryParameter] ?int $page,
        #[MapQueryParameter] ?int $limit,
        #[MapQueryParameter] ?string $sort,
        #[MapQueryParameter] ?string $order,
        #[MapQueryParameter] ?int    $eventId = null,
        #[MapQueryParameter] ?int    $roundId = null,
        #[MapQueryParameter] ?string $name = null,
        #[MapQueryParameter] ?string $email = null,
        #[MapQueryParameter] ?string $phone = null,
        #[MapQueryParameter] ?string $licenceNumber = null,
        #[MapQueryParameter] ?string $asaCode = null,
        #[MapQueryParameter] ?int    $typeId = null,
        #[MapQueryParameter] ?bool   $isFlag = null,
    ): JsonResponse
    {
        $commissaires = $commissaireBusiness->getCommissaires(
            $page ?? 1,
            $limit ?? 20, $sort,
            $order,
            $eventId,
            $roundId,
            $name,
            $email,
            $phone,
            $licenceNumber,
            $asaCode,
            $typeId,
            $isFlag
        );

        return $this->json($commissaires, Response::HTTP_OK, [], ['groups' => ['commissaire', 'type', 'commissaireType', 'commissaireRound', 'round', 'roundDetails', 'roundDetail', 'roundEvent', 'event']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createCommissaire(
        CommissaireBusiness $commissaireBusiness,
        #[MapRequestPayload] CreateCommissaireDto $commissaireDto
    ): Response
    {
        $commissaireBusiness->createCommissaire($commissaireDto);

        return new Response();
    }

    #[Route('/{commissaire}', name: 'update', methods: ['PUT'])]
    public function updateCommissaire(
        CommissaireBusiness $commissaireBusiness,
        Commissaire $commissaire,
        #[MapRequestPayload] CommissaireDto $commissaireDto
    ): Response
    {
        $commissaireBusiness->updatePersonCommissaire($commissaire, $commissaireDto);

        return new Response();
    }

    #[Route('/{commissaire}', name: 'delete', methods: ['DELETE'])]
    public function deleteCommissaire(
        CommissaireBusiness $commissaireBusiness,
        Commissaire $commissaire
    ): Response
    {
        $commissaireBusiness->deleteCommissaire($commissaire);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}