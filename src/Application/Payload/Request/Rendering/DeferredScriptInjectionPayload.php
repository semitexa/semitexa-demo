<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/deferred-scripts',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Script Injection',
    slug: 'deferred-scripts',
    summary: 'Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.',
    order: 7,
    highlights: ['clientModules', 'semitexa:block:rendered', 'auto-play', 'script isolation'],
    entryLine: 'Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.',
    learnMoreLabel: 'See the clientModules pattern →',
    deepDiveLabel: 'Block lifecycle events →',
)]
class DeferredScriptInjectionPayload
{
}
