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
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Reactive Import',
    slug: 'reactive-import',
    summary: 'A product import job ticks every minute — the row counter animates in real time.',
    order: 10,
    highlights: ['refreshInterval: 2', 'batch processing', 'progress_percent', 'heartbeat tick'],
    entryLine: 'A product import job ticks every minute — the row counter animates in real time.',
    learnMoreLabel: 'See batch config →',
    deepDiveLabel: 'Heartbeat tick internals →',
)]
class ReactiveImportPayload
{
}
