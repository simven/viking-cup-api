<?php

namespace App\Dto;

class PenaltyDto
{
    public function __construct(
        public int $points,
        public int $penaltyReasonId,
        public ?int $id = null,
    )
    {}
}