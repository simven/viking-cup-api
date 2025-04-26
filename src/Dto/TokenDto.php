<?php

namespace App\Dto;

class TokenDto
{
    public function __construct(
        public string $token
    )
    {}
}