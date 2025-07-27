<?php

namespace App\Controller\Api;

use App\Business\VolunteerBusiness;
use App\Dto\VolunteerDto;
use App\Entity\Volunteer;
use App\Entity\Round;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/volunteers', name: 'api_volunteers')]
class VolunteerApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getVolunteers(
        VolunteerBusiness $volunteerBusiness,
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
        $volunteers = $volunteerBusiness->getVolunteers(
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

        return $this->json($volunteers, Response::HTTP_OK, [], ['groups' => ['volunteer', 'volunteerRound', 'round', 'roundDetails', 'roundDetail', 'roundEvent', 'event']]);
    }

    #[Route('/{round}', name: 'create', methods: ['POST'])]
    public function createVolunteer(
        VolunteerBusiness $volunteerBusiness,
        Round $round,
        #[MapRequestPayload] VolunteerDto $volunteerDto
    ): Response
    {
        $volunteerBusiness->createPersonVolunteer($round, $volunteerDto);

        return new Response();
    }

    #[Route('/{volunteer}', name: 'update', methods: ['POST'])]
    public function updateVolunteer(
        VolunteerBusiness $volunteerBusiness,
        Volunteer $volunteer,
        #[MapRequestPayload] VolunteerDto $volunteerDto
    ): Response
    {
        $volunteerBusiness->updatePersonVolunteer($volunteer, $volunteerDto);

        return new Response();
    }

    #[Route('/{volunteer}', name: 'delete', methods: ['DELETE'])]
    public function deleteVolunteer(
        VolunteerBusiness $volunteerBusiness,
        Volunteer $volunteer
    ): Response
    {
        $volunteerBusiness->deleteVolunteer($volunteer);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}