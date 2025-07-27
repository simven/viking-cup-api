<?php

namespace App\Dto;

class CreateRescuerDto
{
    public function __construct(
        public int $personId,
        public int $roundId,
        public ?string $role = null,
    )
    {}
}