<?php

namespace App\Filament\Concerns;

trait ChecksPagePermission
{
    abstract protected static function permissionArea(): string;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        $area = static::permissionArea();

        return (bool) $user?->can("view {$area}") || (bool) $user?->can("manage {$area}");
    }
}
