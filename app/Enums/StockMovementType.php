<?php

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockMovementType: string implements HasColor, HasLabel
{
    use EnumHelpers;

    case Restock = 'restock';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case Return = 'return';
    case Cancel = 'cancel';

    public function getLabel(): string
    {
        return match ($this) {
            self::Restock => 'Restock',
            self::Sale => 'Sale',
            self::Adjustment => 'Adjustment',
            self::Return => 'Return',
            self::Cancel => 'Order Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Restock => 'success',
            self::Sale => 'info',
            self::Adjustment => 'gray',
            self::Return => 'warning',
            self::Cancel => 'warning',
        };
    }
}
