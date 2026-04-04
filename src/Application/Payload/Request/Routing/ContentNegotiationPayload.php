<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Request;
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
    order: 6,
    highlights: ['#[AsPayload(produces)]', 'Accept header', '?_format= override', 'ContentNegotiator'],
    entryLine: 'One endpoint serves JSON or HTML depending on the Accept header — no branching in handler code.',
    learnMoreLabel: 'Toggle formats →',
    deepDiveLabel: 'How negotiation works →',
)]
class ContentNegotiationPayload
{
    protected ?Request $httpRequest = null;
    protected string $format = '';
    protected ?string $expand = null;
    protected ?string $slot = null;

    public function getHttpRequest(): ?Request
    {
        return $this->httpRequest;
    }

    public function setHttpRequest(Request $httpRequest): void
    {
        $this->httpRequest = $httpRequest;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getExpand(): ?string
    {
        return $this->expand;
    }

    public function setExpand(?string $expand): void
    {
        $this->expand = $expand !== null ? trim($expand) : null;
    }

    public function getSlot(): ?string
    {
        return $this->slot;
    }

    public function setSlot(?string $slot): void
    {
        $this->slot = $slot !== null ? trim($slot) : null;
    }
}
