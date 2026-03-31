<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Api\Attributes\ApiVersion;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/v0/products',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[ExternalApi(version: 'v0', description: 'Sunset demo product collection endpoint')]
#[ApiVersion(version: '0.9.0', deprecatedSince: '2025-06-01', sunsetDate: '2026-06-01')]
#[DemoFeature(
    section: 'api',
    title: 'Sunset Version',
    slug: 'sunset-version',
    summary: 'A deprecated product endpoint that emits both Deprecation and Sunset headers.',
    order: 5,
    highlights: ['#[ApiVersion]', 'Deprecation', 'Sunset', 'lifecycle headers'],
    entryLine: 'Version lifecycle in Semitexa lives in metadata headers, not route forks and tribal knowledge.',
    learnMoreLabel: 'See sunset headers →',
    deepDiveLabel: 'Version policy internals →',
)]
final class ProductListV0Payload
{
    protected ?Request $httpRequest = null;
    protected ?string $q = null;
    protected ?string $format = null;

    public function getHttpRequest(): ?Request { return $this->httpRequest; }
    public function setHttpRequest(Request $httpRequest): void { $this->httpRequest = $httpRequest; }
    public function getQ(): ?string { return $this->q; }
    public function setQ(?string $q): void { $this->q = $q !== null ? trim($q) : null; }
    public function getFormat(): ?string { return $this->format; }
    public function setFormat(?string $format): void { $this->format = $format !== null ? trim($format) : null; }
}
