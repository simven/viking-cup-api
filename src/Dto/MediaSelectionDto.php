<?php

namespace App\Dto;

class MediaSelectionDto
{
    public function __construct(
        public bool $selected
    )
    {}
}