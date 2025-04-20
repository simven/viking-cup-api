<?php

namespace App\Dto;

class PenaltyDto
{
    public function __construct(
        public ?int $id = null,
        public int $points,
        public int $penaltyReasonId
    )
    {}
}