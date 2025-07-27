<?php

namespace App\Dto;

class CreatePilotDto
{
    public function __construct(
        public int     $personId,
        public ?bool   $ffsaLicensee = null,
        public ?string $ffsaNumber = null,
        public array   $participations = [],
        public int     $eventId,
        public ?int    $number = null,
        public bool    $receiveWindscreenBand = false,
    )
    {}
}