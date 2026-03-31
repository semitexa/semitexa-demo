<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/component-scripts',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Component Script Assets',
    slug: 'component-scripts',
    summary: 'A Semitexa SSR component can own its optional enhancement asset, so behavior travels with the component instead of leaking into page-level glue.',
    order: 5,
    highlights: ['#[AsComponent]', 'script', 'SemitexaComponent.register()', 'auto-require', 'SSR component root'],
    entryLine: 'No more “remember to include the JS somewhere on this page”. If a component needs optional client enhancement, the contract lives on the component itself.',
    learnMoreLabel: 'See the enhancement contract →',
    deepDiveLabel: 'Inspect auto-mount behavior →',
)]
final class ComponentScriptAssetPayload
{
}
