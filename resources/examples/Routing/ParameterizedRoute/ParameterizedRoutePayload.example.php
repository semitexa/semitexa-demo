<?php

declare(strict_types=1);

namespace App\Application\Payload\Routing;

use App\Application\Resource\Page\ProductPageResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/products/{slug}',
    methods: ['GET'],
    responseWith: ProductPageResource::class,
    requirements: ['slug' => '[a-z0-9-]+'],
)]
final class ParameterizedRoutePayload
{
    protected string $slug = '';

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
