<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class FamilyDoctorDto extends Data
{
    public function __construct(
        public ?string $Organisation,
        public ?string $Title,
        public ?string $FirstName,
        public ?string $MiddleName,
        public ?string $Surname,
        public ?string $AddressLine1,
        public ?string $AddressLine2,
        public ?string $AddressLine3,
        public ?string $AddressLine4,
        public ?string $PostCode,
        public ?string $CountryCode
    ) {
    }
}
