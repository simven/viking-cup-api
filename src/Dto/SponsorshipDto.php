<?php

namespace App\Dto;

use App\Enum\SponsorshipStatus;

class SponsorshipDto
{
    public function __construct(
        public ?int $id = null,
        public ?int $eventId = null,
        public ?int $roundId = null,
        public SponsorshipStatus $status,
        public array $counterparts = []
    )
    {}
}