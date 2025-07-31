<?php

namespace App\Dto;

class CommissaireDto
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public ?string $comment = null,
        public int $warnings = 0,
        public array $presence = [],
        public ?int $roundId = null,
        public ?string $licenceNumber = null,
        public ?string $asaCode = null,
        public ?int $typeId = null,
        public bool $isFlag = false,
    )
    {}
}