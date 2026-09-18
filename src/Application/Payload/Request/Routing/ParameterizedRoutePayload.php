<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/routing/parameterized/{slug}',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['text/html', 'application/json'],
    requirements: ['slug' => '[a-z0-9-]+'],
    defaults: ['slug' => 'headphones'],
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
