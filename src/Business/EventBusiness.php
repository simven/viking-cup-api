<?php

namespace App\Business;

use App\Repository\EventRepository;

readonly class EventBusiness
{
    public function __construct(
        private EventRepository $eventRepository
    )
    {}

    public function getEvents(): array
    {
        return $this->eventRepository->findAll();
    }
}