<?php

declare(strict_types=1);

namespace App\Application\Payload\Data;

use App\Application\Resource\Page\MerchandisingCatalogResource;
use Semitexa\Core\Attribute\AsPublicPayload;

#[AsPublicPayload(
    path: '/catalog/campaigns/current',
    methods: ['GET'],
    responseWith: MerchandisingCatalogResource::class,
)]
final class MerchandisingCatalogPayload
{
}
