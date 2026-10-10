<?php

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EngravingType: string implements HasColor, HasLabel
{
    use EnumHelpers;

    case Name = 'name';
    case Initials = 'initials';
    case Quote = 'quote';
    case Logo = 'logo';

    public function getLabel(): string
    {
        return match ($this) {
            self::Name => 'Name',
            self::Initials => 'Initials',
            self::Quote => 'Quote',
            self::Logo => 'Logo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Name => 'gray',
            self::Initials => 'gray',
            self::Quote => 'gray',
            self::Logo => 'gray',
        };
    }
}
