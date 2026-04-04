<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Payload\Api\ProductListV2Payload;
use App\Application\Resource\Api\ProductListV2Resource;
use App\Domain\Api\ProductApiReaderInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ProductListV2Payload::class, resource: ProductListV2Resource::class)]
final class ProductListV2Handler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ProductApiReaderInterface $reader;

    public function handle(ProductListV2Payload $payload, ProductListV2Resource $resource): ProductListV2Resource
    {
        return $resource->fromProducts($this->reader->listCurrentVersion());
    }
}
