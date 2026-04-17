<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantResolutionResource;

#[PublicEndpoint]
#[AsPayload(
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
