<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class PatientDetailDto extends Data
{
    public function __construct(
        public PatientDto $Patient,
        public FamilyDoctorDto $FamilyDoctor
    ) {
    }
}
