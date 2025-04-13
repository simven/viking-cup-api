<?php

namespace App\Dto;

class QualifDto
{
    public function __construct(
        public ?int $pilotRoundCategoryId = null,
        public ?int $passage = null,
        public ?int $points = null
    )
    {}
}