<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentType: string implements HasLabel
{
    case Lease = 'lease';
    case Id = 'id';
    case IncomeProof = 'income_proof';
    case Insurance = 'insurance';
    case Inspection = 'inspection';
    case Receipt = 'receipt';
    case Notice = 'notice';
    case Warranty = 'warranty';
    case Tax = 'tax';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Lease => 'Lease',
            self::Id => 'ID / KYC',
            self::IncomeProof => 'Income proof',
            self::Insurance => 'Insurance',
            self::Inspection => 'Inspection',
            self::Receipt => 'Receipt',
            self::Notice => 'Notice',
            self::Warranty => 'Warranty',
            self::Tax => 'Tax document',
            self::Other => 'Other',
        };
    }
}
