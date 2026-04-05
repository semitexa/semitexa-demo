<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Api\Attribute\ApiVersion;
use Semitexa\Api\Attribute\ExternalApi;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/errors/{type}',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
    requirements: ['type' => '[a-z-]+'],
    defaults: ['type' => 'not-found'],
    produces: ['application/json', 'text/html'],
)]
#[ExternalApi(version: 'v1', description: 'Demo API error envelope trigger endpoint')]
#[ApiVersion(version: '1.0.0')]
#[DemoFeature(
    section: 'api',
    title: 'Structured Errors',
    slug: 'structured-errors',
    summary: 'Throw domain exceptions and let semitexa-api map them into stable machine-readable error envelopes.',
    order: 7,
    highlights: ['ExternalApiExceptionMapper', 'DomainException', 'error.context', 'request_id'],
    entryLine: 'The error response should be more useful than the stack trace. This route proves the envelope stays structured across failure types.',
    learnMoreLabel: 'Trigger error envelopes →',
    deepDiveLabel: 'Error mapper internals →',
)]
final class ApiErrorTriggerPayload
{
    protected ?Request $httpRequest = null;
    protected string $type = 'not-found';
    protected ?string $format = null;

    public function getHttpRequest(): ?Request { return $this->httpRequest; }
    public function setHttpRequest(Request $httpRequest): void { $this->httpRequest = $httpRequest; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): void { $this->type = trim($type); }
    public function getFormat(): ?string { return $this->format; }
    public function setFormat(?string $format): void { $this->format = $format !== null ? trim($format) : null; }
}
