<?php

namespace App\Enums;

enum Permission: string
{
    case ViewProducts = 'view products';
    case ManageProducts = 'manage products';
    case ViewOrders = 'view orders';
    case ManageOrders = 'manage orders';
    case ViewUsers = 'view users';
    case ManageUsers = 'manage users';
    case ManageInventory = 'manage inventory';
    case ManageSettings = 'manage settings';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
