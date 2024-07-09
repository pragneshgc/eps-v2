<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class ProductQuantityDto extends Data
{
    public function __construct(
        public int $Quantity,
        public string $Units,
        public int $Dosage
    ) {
    }
}
