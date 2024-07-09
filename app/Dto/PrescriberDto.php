<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class PrescriberDto extends Data
{
    public function __construct(
        public DoctorDto $Doctor
    ) {
    }
}
