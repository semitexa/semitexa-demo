<?php

declare(strict_types=1);

namespace App\Application\Payload\Data;

use App\Application\Resource\Page\MerchandisingCatalogResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/catalog/campaigns/current',
    methods: ['GET'],
    responseWith: MerchandisingCatalogResource::class,
)]
final class MerchandisingCatalogPayload
{
}
