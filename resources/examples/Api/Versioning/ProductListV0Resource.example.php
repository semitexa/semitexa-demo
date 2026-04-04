<?php

declare(strict_types=1);

namespace App\Application\Resource\Api;

final class ProductListV0Resource
{
    /** @var list<array<string, mixed>> */
    private array $products = [];

    /**
     * @param list<array<string, mixed>> $products
     */
    public function fromProducts(array $products): self
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
