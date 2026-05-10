<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantResolutionResource;

#[AsPublicPayload(
    path: '/demo/platform/tenancy-resolution',
    methods: ['GET'],
    responseWith: DemoTenantResolutionResource::class,
    produces: ['application/json', 'text/html'],
)]
class TenantResolutionPayload
{
    protected ?string $tab = null;

    public function getTab(): ?string { return $this->tab; }
    public function setTab(?string $tab): void { $this->tab = $tab; }
}
