<?php

declare(strict_types=1);

namespace App\Application\Payload\Container;

use App\Application\Resource\Page\MailerStatusResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/docs/di/contracts',
    methods: ['GET'],
    responseWith: MailerStatusResource::class,
)]
final class ServiceContractPayload
{
}
