<?php

declare(strict_types=1);

namespace App\Catalog\Controller;

use Framework\Http\Request;
use Framework\Http\Response;
use App\Catalog\Service\ProductService;

final class ProductShowController
{
    public function __construct(
        private ProductService $products,
    ) {}

    public function __invoke(Request $request): Response
    {
        $slug = (string) $request->query->get('slug', '');

        if ($slug === '') {
            return new Response(
                body: json_encode(['error' => 'Missing slug']),
                status: 422,
                headers: ['Content-Type' => 'application/json'],
            );
        }

        $product = $this->products->findBySlug($slug);

        if ($product === null) {
            return new Response(
                body: json_encode(['error' => 'Not found']),
                status: 404,
                headers: ['Content-Type' => 'application/json'],
            );
        }

        return new Response(
            body: json_encode([
                'slug' => $product->slug,
                'name' => $product->name,
                'price' => $product->price,
            ]),
            headers: ['Content-Type' => 'application/json'],
        );
    }
}
