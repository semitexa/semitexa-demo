<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Api\Attributes\ApiVersion;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/errors/{type}',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
    requirements: ['type' => '[a-z-]+'],
    defaults: ['type' => 'not-found'],
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
    protected string $type = 'not-found';

    public function getType(): string { return $this->type; }
    public function setType(string $type): void { $this->type = trim($type); }
}
