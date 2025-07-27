<?php

namespace App\Controller\Api;

use App\Business\RescuerBusiness;
use App\Dto\CreateRescuerDto;
use App\Dto\RescuerDto;
use App\Entity\Rescuer;
use App\Entity\Round;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/rescuers', name: 'api_rescuers')]
class RescuerApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getRescuers(
        RescuerBusiness $rescuerBusiness,
        #[MapQueryParameter] ?int $page,
        #[MapQueryParameter] ?int $limit,
        #[MapQueryParameter] ?string $sort,
        #[MapQueryParameter] ?string $order,
        #[MapQueryParameter] ?int    $eventId = null,
        #[MapQueryParameter] ?int    $roundId = null,
        #[MapQueryParameter] ?string $name = null,
        #[MapQueryParameter] ?string $email = null,
        #[MapQueryParameter] ?string $phone = null,
        #[MapQueryParameter] ?string $role = null
    ): JsonResponse
    {
        $rescuers = $rescuerBusiness->getRescuers(
            $page ?? 1,
            $limit ?? 20, $sort,
            $order,
            $eventId,
            $roundId,
            $name,
            $email,
            $phone,
            $role
        );

        return $this->json($rescuers, Response::HTTP_OK, [], ['groups' => ['rescuer', 'rescuerRound', 'round', 'roundDetails', 'roundDetail', 'roundEvent', 'event']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createRescuer(
        RescuerBusiness $rescuerBusiness,
        #[MapRequestPayload] CreateRescuerDto $rescuerDto
    ): Response
    {
        $rescuerBusiness->createRescuer($rescuerDto);

        return new Response();
    }

    #[Route('/{rescuer}', name: 'update', methods: ['PUT'])]
    public function updateRescuer(
        RescuerBusiness $rescuerBusiness,
        Rescuer $rescuer,
        #[MapRequestPayload] RescuerDto $rescuerDto
    ): Response
    {
        $rescuerBusiness->updatePersonRescuer($rescuer, $rescuerDto);

        return new Response();
    }

    #[Route('/{rescuer}', name: 'delete', methods: ['DELETE'])]
    public function deleteRescuer(
        RescuerBusiness $rescuerBusiness,
        Rescuer $rescuer
    ): Response
    {
        $rescuerBusiness->deleteRescuer($rescuer);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}