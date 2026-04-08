<?php

declare(strict_types=1);

namespace App\Application\Handler\PayloadHandler\Data;

use App\Application\Db\Repository\ProductRepository;
use App\Application\Payload\Request\Data\RelationsPayload;
use App\Application\Resource\Response\RelationsPageResource;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: RelationsPayload::class, resource: RelationsPageResource::class)]
final class RelationsHandler implements TypedHandlerInterface
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function handle(RelationsPayload $payload, RelationsPageResource $resource): RelationsPageResource
    {
        $items = $this->products->findPage(12);
        $product = $items[0] ?? null;

        return $resource->fromArray([
            'product' => $product?->name,
            'category' => $product?->category?->name,
            'reviews' => count($product?->reviews ?? []),
            'firstReviewProduct' => $product?->reviews[0]?->product?->name,
        ]);
    }
}
