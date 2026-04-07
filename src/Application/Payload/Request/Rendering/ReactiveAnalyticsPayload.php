<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/reactive-analytics',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Reactive Analytics',
    slug: 'reactive-analytics',
    summary: 'Independent analytics jobs can light up one dashboard progressively, while the page stays server-rendered from the first byte.',
    order: 12,
    highlights: ['multi-job snapshots', 'independent panel refresh', 'refreshInterval: 5', 'SSR-first live UI'],
    entryLine: 'Each panel updates when its own job finishes, so the dashboard feels live without turning into a client-side orchestration layer.',
    learnMoreLabel: 'See the dashboard contract →',
    deepDiveLabel: 'How multi-job panels stay coherent →',
)]
class ReactiveAnalyticsPayload
{
}
