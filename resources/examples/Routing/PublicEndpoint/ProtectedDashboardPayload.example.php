<?php

declare(strict_types=1);

namespace App\Payload\Routing;

use Semitexa\Core\Attribute\AsPayload;
use App\Resource\DashboardPageResource;

#[AsPayload(
    responseWith: DashboardPageResource::class,
    path: '/dashboard',
    methods: ['GET'],
)]
final class ProtectedDashboardPayload
{
}
