<?php

namespace App\Dto;

class RoundCategoryPilotsQualifyingDto
{
    public function __construct(
        public int $id,
        public int $pilotId,
        public bool $isCompeting,
        public array $qualifs = []
    )
    {}
}