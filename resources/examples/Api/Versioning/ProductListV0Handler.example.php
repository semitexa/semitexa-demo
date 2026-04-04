<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Payload\Api\ProductListV0Payload;
use App\Application\Resource\Api\ProductListV0Resource;
use App\Domain\Api\ProductApiReaderInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ProductListV0Payload::class, resource: ProductListV0Resource::class)]
final class ProductListV0Handler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ProductApiReaderInterface $reader;

    public function handle(ProductListV0Payload $payload, ProductListV0Resource $resource): ProductListV0Resource
    {
        return $resource->fromProducts($this->reader->listLegacyVersion());
    }
}
