<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/philosophy',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'SSR Philosophy',
    slug: 'philosophy',
    summary: 'Semitexa SSR is one continuous rendering architecture: page, slots, deferred regions, live refresh, and interactive components stay inside one server-owned story.',
    order: 0,
    highlights: ['one rendering story', 'HtmlResponse', 'Slot Resources', 'Deferred SSR', 'Reactive SSR'],
    entryLine: 'Semitexa SSR is not “render once on the server and then improvise”. It is a coherent rendering system that refuses to split the page into backend HTML plus frontend survival code.',
    learnMoreLabel: 'See the Semitexa SSR axioms →',
    deepDiveLabel: 'What Semitexa SSR refuses to become →',
)]
final class SsrPhilosophyPayload
{
}
