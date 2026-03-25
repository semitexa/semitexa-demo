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
    title: 'Layout Slots',
    slug: 'slots',
    summary: 'Fill named layout regions from independent slot resources — zero coupling between page regions.',
    order: 2,
    highlights: ['#[AsSlotResource]', 'HtmlSlotResponse', 'layout_slot()', 'slot handle', 'independent rendering'],
    entryLine: 'Fill named layout regions from independent slot resources — zero coupling between page regions.',
    learnMoreLabel: 'See slot registration →',
    deepDiveLabel: 'Slot resolution order →',
)]
class LayoutSlotPayload
{
}
