<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/routing/typed-handler',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'routing',
    title: 'Typed Handler',
    slug: 'typed-handler',
    summary: 'Concrete types in handle() — no instanceof, no casting, no guessing.',
    order: 4,
    highlights: ['TypedHandlerInterface', '#[AsPayloadHandler]', 'HandlerReflectionCache', 'concrete types'],
    entryLine: 'Handlers declare concrete Payload and Resource types — the framework validates signatures at boot.',
    learnMoreLabel: 'See the typed signature →',
    deepDiveLabel: 'How reflection validation works →',
)]
class TypedHandlerPayload
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
