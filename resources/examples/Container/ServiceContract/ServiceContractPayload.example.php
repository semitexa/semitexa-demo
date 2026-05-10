<?php

declare(strict_types=1);

namespace App\Application\Payload\Container;

use App\Application\Resource\Page\MailerStatusResource;
use Semitexa\Core\Attribute\AsPublicPayload;

#[AsPublicPayload(
    path: '/docs/di/contracts',
    methods: ['GET'],
    responseWith: MailerStatusResource::class,
)]
final class ServiceContractPayload
{
}
