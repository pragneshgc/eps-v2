<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class ProductDto extends Data
{
    public function __construct(
        public string $ProductCode,
        public string $Description,
        public ProductQuantityDto $ProductQuantity,
        public string $Instructions,
        public ?string $Instructions2,
    ) {
    }
}
