<?php

declare(strict_types=1);

namespace App\Application\Handler\Routing;

use App\Application\Payload\Routing\ParameterizedRoutePayload;
use App\Application\Resource\Page\ProductPageResource;
use App\Domain\Catalog\ProductCatalogReaderInterface;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ParameterizedRoutePayload::class, resource: ProductPageResource::class)]
final class ParameterizedRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ProductCatalogReaderInterface $catalogReader;

    public function handle(ParameterizedRoutePayload $payload, ProductPageResource $resource): ProductPageResource
    {
        return $resource->fromProduct($this->catalogReader->findBySlug($payload->getSlug()));
    }
}
