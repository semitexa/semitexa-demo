<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Payload\Api\ProductListPayload;
use App\Application\Resource\Api\ProductListResource;
use App\Domain\Api\ProductApiReaderInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ProductListPayload::class, resource: ProductListResource::class)]
final class MachineAuthHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ProductApiReaderInterface $reader;

    public function handle(ProductListPayload $payload, ProductListResource $resource): ProductListResource
    {
        return $resource->fromProducts($this->reader->listForApi());
    }
}
