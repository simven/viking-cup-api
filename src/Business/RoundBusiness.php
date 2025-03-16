<?php

namespace App\Business;

use App\Repository\RoundRepository;

readonly class RoundBusiness
{
    public function __construct(
        private RoundRepository $roundRepository
    )
    {}

    public function getRounds(): array
    {
        return $this->roundRepository->findAll();
    }
}