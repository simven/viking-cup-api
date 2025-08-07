<?php

namespace App\Dto;

use App\Enum\SponsorCounterpartType;

class SponsorshipCounterpartDto
{
    public function __construct(
        public ?int $id = null,
        public SponsorCounterpartType $counterpartType,
        public float|int|null $amount = null,
        public ?string $otherCounterpart = null
    )
    {}
}