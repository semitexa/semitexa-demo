<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/seo',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'SEO',
    slug: 'seo',
    summary: 'Set title, description, and Open Graph tags from your handler — no template hacks needed.',
    order: 4,
    highlights: ['pageTitle()', 'withMeta()', 'Open Graph', 'canonical URL', 'structured data'],
    entryLine: 'Set title, description, and Open Graph tags from your handler — no template hacks needed.',
    learnMoreLabel: 'See SEO methods →',
    deepDiveLabel: 'SEO pipeline internals →',
)]
class SeoPayload
{
}
