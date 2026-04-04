<?php

declare(strict_types=1);

namespace App\Application\Resource\Api;

final class ProductListResource
{
    /**
     * @param list<array<string, mixed>> $products
     */
    public function fromProducts(array $products): self
    {
        return $this;
    }
}
