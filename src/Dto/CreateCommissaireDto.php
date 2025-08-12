<?php

namespace App\Dto;

class CreateCommissaireDto
{
    public function __construct(
        public int $personId,
        public int $roundId,
        public ?string $licenceNumber = null,
        public ?string $asaCode = null,
        public ?int $typeId = null,
        public bool $isFlag = false,
    )
    {}
}