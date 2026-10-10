<?php

namespace App\Filament\Concerns;

use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Role-based access for Filament resources. Read actions need "view {area}" or
 * "manage {area}"; every write action needs "manage {area}".
 */
trait ChecksPermission
{
    abstract protected static function permissionArea(): string;

    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $user = auth()->user();
        $action = $action instanceof UnitEnum ? $action->name : $action;
        $area = static::permissionArea();

        if (! $user) {
            return Response::deny();
        }

        $allowed = in_array($action, ['viewAny', 'view'], true)
            ? $user->can("view {$area}") || $user->can("manage {$area}")
            : $user->can("manage {$area}");

        return $allowed ? Response::allow() : Response::deny('You do not have permission for this action.');
    }
}
