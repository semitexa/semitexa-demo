<?php

declare(strict_types=1);

namespace App\Auth\Session;

final class SessionRoleMatrix
{
    public const ROLES = [
        'admin' => [
            'label' => 'Admin',
            'permissions' => ['products.read', 'products.write', 'users.manage', 'orders.manage', 'settings.manage'],
        ],
        'editor' => [
            'label' => 'Editor',
            'permissions' => ['products.read', 'products.write'],
        ],
        'viewer' => [
            'label' => 'Viewer',
            'permissions' => ['products.read'],
        ],
    ];
}
