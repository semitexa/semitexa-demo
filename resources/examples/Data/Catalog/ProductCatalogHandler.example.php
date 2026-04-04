<?php

declare(strict_types=1);

namespace App\Application\Handler\Data;

use App\Application\Payload\Data\ProductCatalogPayload;
use App\Application\Resource\Page\ProductCatalogResource;
use App\Domain\Catalog\ProductReadRepositoryInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ProductCatalogPayload::class, resource: ProductCatalogResource::class)]
final class ProductCatalogHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ProductReadRepositoryInterface $products;

    public function handle(ProductCatalogPayload $payload, ProductCatalogResource $resource): ProductCatalogResource
    {
        return $resource->fromProducts($this->products->findForCatalog($payload));
    }
}
