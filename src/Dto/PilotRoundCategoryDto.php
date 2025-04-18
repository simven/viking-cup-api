<?php

namespace App\Dto;

class PilotRoundCategoryDto
{
    public function __construct(
        public bool $isCompeting,
        public bool $isEngaged
    )
    {}
}