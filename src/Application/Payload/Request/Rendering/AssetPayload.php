<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/assets',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Asset Pipeline',
    slug: 'assets',
    summary: 'Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.',
    order: 5,
    highlights: ['assets.json', 'asset_head()', 'asset_body()', 'glob patterns', 'versioning'],
    entryLine: 'Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.',
    learnMoreLabel: 'See the asset manifest →',
    deepDiveLabel: 'Asset pipeline internals →',
)]
class AssetPayload
{
}
