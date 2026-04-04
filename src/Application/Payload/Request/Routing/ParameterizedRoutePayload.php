<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/routing/product/{slug}',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
    requirements: ['slug' => '[a-z0-9-]+'],
    defaults: ['slug' => 'headphones'],
)]
#[DemoFeature(
    section: 'routing',
    title: 'Parameterized Route',
    slug: 'parameterized',
    summary: 'Path parameters with regex constraints and typed injection.',
    order: 5,
    highlights: ['requirements', 'defaults', 'PayloadHydrator', 'setter injection'],
    entryLine: 'Path parameters like {slug} are extracted and injected via setters — with regex validation at the router level.',
    learnMoreLabel: 'Try different slugs →',
    deepDiveLabel: 'How regex compilation works →',
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
