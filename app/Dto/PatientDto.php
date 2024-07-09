<?php

namespace App\Dto;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Date;

class PatientDto extends Data
{
    public function __construct(
        public PatientIdDto $PatientId,
        public PatientNameDto $PatientName,
        public string $DOB,
        public int $Sex,
        public float $BMI,
        public AddressDto $HomeAddress,
        public AddressDto $DeliveryAddress,
        public string $SaturdayDelivery,
        public string $UPSAccessPointDelivery,
        public ?string $Notes,
        public string $Telephone,
        public string $Mobile,
        public string $Email
    ) {
    }
}
