<?php

namespace App\Controller\Api;

use App\Business\EventBusiness;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/events', name: 'api_events')]
class EventApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getEvents(
        EventBusiness $eventBusiness
    ): Response
    {
        $events = $eventBusiness->getEvents();

        return $this->json($events, 200, [], ['groups' => ['event', 'eventRounds', 'round', 'roundDetails', 'roundDetail']]);
    }
}