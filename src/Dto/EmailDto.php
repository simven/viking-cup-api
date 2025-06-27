<?php

namespace App\Dto;

class EmailDto {
    public function __construct(
        public ?string $fromName = null,
        public ?string $fromEmail = null,
        public ?string $to = null,
        public ?string $subject = null,
        public ?string $message = null,
        public ?string $attachment = null,
    )
    {}
}
