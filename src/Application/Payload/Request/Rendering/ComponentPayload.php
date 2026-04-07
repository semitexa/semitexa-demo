<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/components',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Components',
    slug: 'components',
    summary: 'Reusable, attribute-registered UI components — discovered automatically from the classmap.',
    order: 4,
    highlights: ['#[AsComponent]', 'ComponentRegistry', 'props', 'Twig template', 'ClassDiscovery'],
    entryLine: 'Reusable, attribute-registered UI components — discovered automatically from the classmap.',
    learnMoreLabel: 'See component registration →',
    deepDiveLabel: 'How Twig compilation works →',
)]
class ComponentPayload
{
}
