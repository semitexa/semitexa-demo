<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Api\Attributes\ApiVersion;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/v1/products/_schema',
    methods: ['GET'],
    responseWith: DemoApiResponse::class,
    produces: ['application/json', 'application/schema+json'],
)]
#[ExternalApi(version: 'v1', description: 'Demo product schema discovery endpoint')]
#[ApiVersion(version: '1.0.0')]
final class ProductSchemaPayload
{
    protected ?Request $httpRequest = null;

    public function getHttpRequest(): ?Request { return $this->httpRequest; }
    public function setHttpRequest(Request $httpRequest): void { $this->httpRequest = $httpRequest; }
}
