<?php

declare(strict_types=1);

namespace App\Application\Handler\Routing;

use App\Application\Payload\Routing\ContentNegotiationPayload;
use App\Application\Resource\Page\ProductCollectionPageResource;
use App\Domain\Catalog\ProductCatalogReaderInterface;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ContentNegotiationPayload::class, resource: ProductCollectionPageResource::class)]
final class ContentNegotiationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ProductCatalogReaderInterface $catalogReader;

    public function handle(ContentNegotiationPayload $payload, ProductCollectionPageResource $resource): ProductCollectionPageResource
    {
        return $resource->fromProducts($this->catalogReader->listVisibleProducts());
    }
}
