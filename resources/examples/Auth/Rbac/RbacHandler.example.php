<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Payload\Admin\UpdateProductPayload;
use App\Application\Resource\Admin\ProductWriteResource;
use App\Domain\Catalog\ProductWriterInterface;
use Semitexa\Authorization\Attributes\RequiresCapability;
use Semitexa\Authorization\Attributes\RequiresPermission;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[RequiresCapability('backoffice.access')]
#[RequiresPermission('products.write')]
#[AsPayloadHandler(payload: UpdateProductPayload::class, resource: ProductWriteResource::class)]
final class RbacHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ProductWriterInterface $writer;

    public function handle(UpdateProductPayload $payload, ProductWriteResource $resource): ProductWriteResource
    {
        return $resource->fromProduct($this->writer->updateFromPayload($payload));
    }
}
