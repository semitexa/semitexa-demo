<?php

declare(strict_types=1);

namespace App\Application\Handler\Routing;

use App\Application\Payload\Routing\EnvRouteOverridePayload;
use App\Application\Resource\Page\CatalogLandingResource;
use App\Domain\Catalog\CatalogRouteInspectorInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: EnvRouteOverridePayload::class, resource: CatalogLandingResource::class)]
final class EnvRouteOverrideHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected CatalogRouteInspectorInterface $routeInspector;

    public function handle(EnvRouteOverridePayload $payload, CatalogLandingResource $resource): CatalogLandingResource
    {
        return $resource
            ->withTitle('Catalog')
            ->withSummary('The payload still owns the route contract, but operations can remap the public URL through one env key.')
            ->withResolvedPath($this->routeInspector->getResolvedPath(EnvRouteOverridePayload::class))
            ->withFallbackPath('/catalog');
    }
}
