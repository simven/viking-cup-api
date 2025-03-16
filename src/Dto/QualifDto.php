<?php

namespace App\Dto;

class QualifDto
{
    public function __construct(
        public ?int $points = null,
        public ?int $passage = null
    )
    {}
}