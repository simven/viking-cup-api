<?php

namespace App\Business;

use App\Entity\Event;
use App\Repository\RoundRepository;

readonly class RoundBusiness
{
    public function __construct(
        private RoundRepository $roundRepository
    )
    {}

    public function getRounds(?Event $event = null): array
    {
        if ($event !== null) {
            return $this->roundRepository->findBy(['event' => $event]);
        }

        return $this->roundRepository->findAll();
    }
}