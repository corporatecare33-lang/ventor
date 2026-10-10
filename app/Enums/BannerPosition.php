<?php

namespace App\Enums;

use App\Enums\Concerns\EnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BannerPosition: string implements HasColor, HasLabel
{
    use EnumHelpers;

    case Hero = 'hero';
    case Promo = 'promo';
    case Announcement = 'announcement';

    public function getLabel(): string
    {
        return match ($this) {
            self::Hero => 'Hero Slider',
            self::Promo => 'Promo Strip',
            self::Announcement => 'Announcement Bar',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Hero => 'primary',
            self::Promo => 'info',
            self::Announcement => 'gray',
        };
    }
}
