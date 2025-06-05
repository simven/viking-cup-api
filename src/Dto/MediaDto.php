<?php

namespace App\Dto;

class MediaDto
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public int $warnings,
        public ?string $instagram = null,
        public ?string $pilotFollow = null,
        public bool $selected = false,
        public array $presence = [],
    )
    {}
}