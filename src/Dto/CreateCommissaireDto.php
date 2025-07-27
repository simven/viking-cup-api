<?php

namespace App\Dto;

class CreateCommissaireDto
{
    public function __construct(
        public int $personId,
        public int $roundId,
        public ?string $licenceNumber = null,
        public ?string $asaCode = null,
        public ?string $type = null,
        public bool $isFlag = false,
    )
    {}
}