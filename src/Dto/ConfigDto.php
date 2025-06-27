<?php

namespace App\Dto;

class ConfigDto {
    public function __construct(
        public string $name,
        public ?string $displayName = null,
        public ?string $value = null,
    )
    {}
}
