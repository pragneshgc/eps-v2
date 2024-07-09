<?php

namespace App\Dto;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class PrescriptionDto extends Data
{
    public function __construct(
        public ?string $PrescriptionNotes,
        public string $CommercialInvoiceValue,
        public PrescriberDto $Prescriber,
        public ?string $Condition,
        public ?string $Frequency,
        public ProductDto $Product,
        #[DataCollectionOf(QuestionnaireDto::class)]
        #[Rule('required', 'array')]
        public DataCollection $Questionnaire,
    ) {
    }
}
