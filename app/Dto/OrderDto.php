<?php

namespace App\Dto;

use DateTime;
use Spatie\LaravelData\Data;

class OrderDto extends Data
{
    public function __construct(
        public int $SenderID,
        public int $ClientID,
        public string $Date,
        public int $PharmacyID,
        public PatientDetailDto $PatientDetail,
        public PrescriptionDto $Prescription
    ) {
    }
}
