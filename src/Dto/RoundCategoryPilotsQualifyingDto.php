<?php

namespace App\Dto;

class RoundCategoryPilotsQualifyingDto
{
    public function __construct(
        public int $id,
        public int $pilotId,
        public array $qualifs = []
    )
    {}
}