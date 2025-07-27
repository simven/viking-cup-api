<?php

namespace App\Dto;

class CreateMediaDto
{
    public function __construct(
        public int $personId,
        public int $roundId,
        public ?string $pilotFollow = null,
        public bool $selected = false,
    )
    {}
}