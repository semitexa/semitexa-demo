<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/slots',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Slot Resources',
    slug: 'slots',
    summary: 'Each page region is its own resource pipeline with the same template system as the main page — no scattered partial glue, no mystery wiring.',
    order: 3,
    highlights: ['#[AsSlotResource]', 'HtmlSlotResponse', 'layout_slot()', 'SlotHandlerPipeline', 'shared Twig'],
    entryLine: 'A slot is not a fragment hack. It is a real resource with its own handler pipeline, template, and lifecycle.',
    learnMoreLabel: 'See the slot pipeline →',
    deepDiveLabel: 'Why unified templates matter →',
)]
class LayoutSlotPayload
{
}
