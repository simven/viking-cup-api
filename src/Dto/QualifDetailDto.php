<?php

namespace App\Dto;

class QualifDetailDto
{
    public function __construct(
        public ?int $qualifyingCriteriaId = null,
        public ?int $points = null,
        public ?string $comment = null,
    )
    {}
}