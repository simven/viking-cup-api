<?php

namespace App\Dto;

class QualifDto
{
    public function __construct(
        public bool $isValid = true
    )
    {}
}