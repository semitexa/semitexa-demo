<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Api\Attribute\ApiVersion;
use Semitexa\Api\Attribute\ExternalApi;
use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/structured-errors',
    methods: ['GET', 'POST'],
    defaults: ['type' => 'not-found'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[ExternalApi(version: 'v1', description: 'Demo API error envelope trigger endpoint')]
#[ApiVersion(version: '1.0.0')]
final class ApiErrorTriggerPayload
{
    protected ?Request $httpRequest = null;
    protected string $type = 'not-found';
    protected ?string $format = null;

    public function getHttpRequest(): ?Request { return $this->httpRequest; }
    public function setHttpRequest(Request $httpRequest): void { $this->httpRequest = $httpRequest; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): void
    {
        $type = strtolower(trim($type));
        $this->type = preg_match('/^[a-z-]+$/', $type) === 1 ? $type : 'not-found';
    }
    public function getFormat(): ?string { return $this->format; }
    public function setFormat(?string $format): void { $this->format = $format !== null ? trim($format) : null; }
}
