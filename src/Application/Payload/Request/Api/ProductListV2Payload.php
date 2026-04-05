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
    path: '/demo/api/v2/products',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[ExternalApi(version: 'v2', description: 'Active demo product collection endpoint')]
#[ApiVersion(version: '2.0.0')]
#[DemoFeature(
    section: 'api',
    title: 'Active Version',
    slug: 'active-version',
    summary: 'The current collection endpoint with a clean X-Api-Version header and no deprecation noise.',
    order: 6,
    highlights: ['#[ApiVersion]', 'X-Api-Version', 'active lifecycle'],
    entryLine: 'The healthy path is boring on purpose: one stable version header, no sunset or migration warnings.',
    learnMoreLabel: 'See active version output →',
    deepDiveLabel: 'Version metadata internals →',
)]
final class ProductListV2Payload
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
