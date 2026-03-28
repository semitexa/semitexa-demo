<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/reactive-import',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Reactive Import',
    slug: 'reactive-import',
    summary: 'Background batches keep moving, and the page reflects server progress as live HTML instead of a client-managed progress app.',
    order: 11,
    highlights: ['refreshInterval: 2', 'server-owned progress', 'batch processing', 'SSR-first live UI'],
    entryLine: 'The import keeps running on the server, and the page stays honest by streaming fresh HTML instead of faking progress in frontend state.',
    learnMoreLabel: 'See the live import contract →',
    deepDiveLabel: 'How server-owned progress stays live →',
)]
class ReactiveImportPayload
{
}
