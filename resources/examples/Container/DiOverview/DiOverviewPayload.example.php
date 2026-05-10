<?php

declare(strict_types=1);

namespace App\Application\Payload\Container;

use App\Application\Resource\Page\DiOverviewResource;
use Semitexa\Core\Attribute\AsPublicPayload;

#[AsPublicPayload(
    path: '/docs/di',
    methods: ['GET'],
    responseWith: DiOverviewResource::class,
)]
final class DiOverviewPayload
{
}
