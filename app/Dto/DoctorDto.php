<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class DoctorDto extends Data
{
    public function __construct(
        public string $GMCNO,
        public string $DoctorName
    ) {
    }
}
