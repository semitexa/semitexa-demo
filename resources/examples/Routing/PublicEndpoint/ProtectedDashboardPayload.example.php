<?php

declare(strict_types=1);

namespace App\Payload\Routing;

use Semitexa\Authorization\Attribute\AsProtectedPayload;
use App\Resource\DashboardPageResource;

#[AsProtectedPayload(
    responseWith: DashboardPageResource::class,
    path: '/dashboard',
    methods: ['GET'],
)]
final class ProtectedDashboardPayload
{
}
