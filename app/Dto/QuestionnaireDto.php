<?php

namespace App\Dto;

use Spatie\LaravelData\Data;

class QuestionnaireDto extends Data
{
    public function __construct(
        public string $Question,
        public ?string $Answer
    ) {
    }
}
