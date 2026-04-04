<?php

declare(strict_types=1);

namespace App\Application\Payload\Container;

use App\Application\Resource\Page\DiOverviewResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/docs/di',
    methods: ['GET'],
    responseWith: DiOverviewResource::class,
)]
final class DiOverviewPayload
{
}
