<?php

declare(strict_types=1);

namespace App\Application\Api;

final class DemoApiPresenter
{
    public function productList(array $products): array
    {
        return [
            'data' => array_map(
                static fn (array $product): array => [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                ],
                $products,
            ),
        ];
    }
}
