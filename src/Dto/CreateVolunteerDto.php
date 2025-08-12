<?php

namespace App\Dto;

class CreateVolunteerDto
{
    public function __construct(
        public int $personId,
        public int $roundId,
        public ?int $roleId = null
    )
    {}
}