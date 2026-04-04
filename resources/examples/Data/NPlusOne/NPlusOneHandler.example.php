<?php

declare(strict_types=1);

namespace App\Application\Handler\Data;

use App\Application\Payload\Data\ProductCardListPayload;
use App\Application\Resource\Page\ProductCardListResource;
use App\Domain\Catalog\ProductCardReadRepositoryInterface;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ProductCardListPayload::class, resource: ProductCardListResource::class)]
final class NPlusOneHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ProductCardReadRepositoryInterface $cards;

    public function handle(ProductCardListPayload $payload, ProductCardListResource $resource): ProductCardListResource
    {
        return $resource->fromCards($this->cards->listCardsWithRelations());
    }
}
