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
    path: '/demo/api/structured-errors',
    methods: ['GET', 'POST'],
    defaults: ['type' => 'not-found'],
    responseWith: DemoFeatureResource::class,
    produces: ['text/html', 'application/json'],
    // Both shapes are declared, because this route really is two things: a
    // page that explains the error envelope, and the endpoint itself. The
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
