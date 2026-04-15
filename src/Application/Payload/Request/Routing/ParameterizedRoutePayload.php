<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/routing/parameterized',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
class ParameterizedRoutePayload
{
    protected string $slug = '';

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
