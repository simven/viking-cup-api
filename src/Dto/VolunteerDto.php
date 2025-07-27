<?php

namespace App\Dto;

class VolunteerDto
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public ?string $instagram = null,
        public ?string $comment = null,
        public int $warnings = 0,
        public ?string $role = null,
        public array $presence = [],
    )
    {}
}