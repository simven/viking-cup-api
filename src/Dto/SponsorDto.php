<?php

namespace App\Dto;

class SponsorDto
{
    public function __construct(
        // Sponsor
        public string $name,
        public ?string $description = null,
        public bool $displayWebsite = false,
        public ?string $alt = null,

        // Contact
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public int $warnings = 0,
        public ?string $comment = null,

        // Sponsorships
        public array $sponsorships = []
    )
    {}
}