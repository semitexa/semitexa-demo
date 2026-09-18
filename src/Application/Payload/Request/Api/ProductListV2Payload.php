<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Api\Attribute\ApiVersion;
use Semitexa\Api\Attribute\ExternalApi;
use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Core\Resource\RenderProfile;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/api/active-version',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['text/html', 'application/json'],
    // Both shapes are declared, because this route really is two things: a
    // page that explains the current collection, and the endpoint itself. The
    // declaration is what tells the renderer that this route's JSON is the
    // handler's own body rather than a page document projected from the render
    // context — without it, the handler built the collection, set its lifecycle
    // headers, and the body was replaced by an empty page envelope.
    //
    // No `responsesByProfile`: one resource serves both, and the handler
    // already decides between them (it also honours `?format=json`, which
    // Accept-driven dispatch could not). Declaring profiles without the map is
    // inert for dispatch by construction — RouteExecutor keeps the single
    // response class.
    renderProfile: [RenderProfile::Html, RenderProfile::Json],
)]
#[ExternalApi(version: 'v2', description: 'Active demo product collection endpoint')]
#[ApiVersion(version: '2.0.0')]
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
