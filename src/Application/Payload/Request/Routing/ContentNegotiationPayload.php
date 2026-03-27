<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
    path: '/demo/routing/products',
    methods: ['GET'],
)]
#[DemoFeature(
    section: 'routing',
    title: 'Content Negotiation',
    slug: 'content-negotiation',
    summary: 'One endpoint, multiple response formats — automatically.',
    order: 5,
    highlights: ['#[AsPayload(produces)]', 'Accept header', '?_format= override', 'ContentNegotiator'],
    entryLine: 'One endpoint serves JSON or HTML depending on the Accept header — no branching in handler code.',
    learnMoreLabel: 'Toggle formats →',
    deepDiveLabel: 'How negotiation works →',
)]
class ContentNegotiationPayload
{
    protected string $format = '';

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }
}
