<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Request;
use Semitexa\Demo\Domain\Repository\DemoCategoryRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoReviewRepositoryInterface;
use Semitexa\Demo\Domain\Model\DemoCategory;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Demo\Domain\Model\DemoReview;

#[AsService]
final class DemoApiPresenter
{
    #[InjectAsReadonly]
    protected DemoProductRepositoryInterface $products;

    #[InjectAsReadonly]
    protected DemoCategoryRepositoryInterface $categories;

    #[InjectAsReadonly]
    protected DemoReviewRepositoryInterface $reviews;

    public function buildCollection(
        Request $request,
        ?string $query = null,
        ?string $status = null,
        int $page = 1,
        int $limit = 8,
        ?string $fields = null,
        ?string $expand = null,
        ?string $profile = null,
        ?string $format = null,
    ): array {
        $representation = $this->resolveRepresentation($request, $format);
        $profileName = $this->resolveProfile($request, $profile);
        $fieldList = $this->parseCsv($fields);
        $expandList = $this->parseExpand($expand);
        $basePath = $this->resolveCollectionBasePath($request);
        $page = max(1, $page);
        $limit = min(24, max(1, $limit));

        $all = $this->products->findFiltered(status: $status, limit: 200, offset: 0);
        if ($query !== null && $query !== '') {
            $needle = mb_strtolower($query);
            $all = array_values(array_filter(
                $all,
                static fn (DemoProduct $product): bool => str_contains(
                    mb_strtolower($product->getName() . ' ' . ($product->getDescription() ?? '')),
                    $needle,
                ),
            ));
        }

        $total = count($all);
        $offset = ($page - 1) * $limit;
        $items = array_slice($all, $offset, $limit);

        $payloadItems = array_map(
            fn (DemoProduct $product): array => $this->presentProduct(
                product: $product,
                profile: $profileName,
                fields: $fieldList,
                expand: $expandList,
                representation: $representation,
                includeLinks: true,
                basePath: $basePath,
            ),
            $items,
        );

        if ($representation === 'json') {
            $payloadItems = array_map(
                static function (array $item) use ($basePath): array {
                    $slug = is_string($item['slug'] ?? null) ? $item['slug'] : null;
                    if ($slug === null || !isset($item['links']) || !is_array($item['links'])) {
                        return $item;
                    }

                    $item['links'] = [
                        'self' => $basePath . '/' . $slug,
                        'collection' => $basePath,
                    ];

                    return $item;
                },
                $payloadItems,
            );
        }

        if ($representation === 'ld+json') {
            return [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Semitexa Demo Product Catalog',
                'numberOfItems' => $total,
                'itemListElement' => array_values(array_map(
                    static fn (array $item, int $index): array => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'item' => $item,
                    ],
                    $payloadItems,
                    array_keys($payloadItems),
                )),
                'hydra:view' => [
                    '@id' => $this->buildCollectionUrl($basePath, $page, $limit, $query, $status, $profileName, $fieldList, $expandList, $format),
                    'hydra:first' => $this->buildCollectionUrl($basePath, 1, $limit, $query, $status, $profileName, $fieldList, $expandList, $format),
                    'hydra:last' => $this->buildCollectionUrl($basePath, max(1, (int) ceil($total / $limit)), $limit, $query, $status, $profileName, $fieldList, $expandList, $format),
                    'hydra:next' => $offset + $limit < $total
                        ? $this->buildCollectionUrl($basePath, $page + 1, $limit, $query, $status, $profileName, $fieldList, $expandList, $format)
                        : null,
                ],
            ];
        }

        return [
            'data' => $payloadItems,
            'meta' => [
                'representation' => 'json',
                'profile' => $profileName,
                'fields' => $fieldList,
                'expand' => $expandList,
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'query' => $query,
                'filters' => array_filter(['status' => $status], static fn (mixed $value): bool => $value !== null && $value !== ''),
            ],
            'links' => [
                'self' => $this->buildCollectionUrl($basePath, $page, $limit, $query, $status, $profileName, $fieldList, $expandList, $format),
            ],
        ];
    }

    public function buildDetail(
        Request $request,
        string $slug,
        ?string $fields = null,
        ?string $expand = null,
        ?string $profile = null,
        ?string $format = null,
    ): ?array {
        $product = $this->findProductBySlug($slug);
        if ($product === null) {
            return null;
        }

        $representation = $this->resolveRepresentation($request, $format);
        $profileName = $this->resolveProfile($request, $profile);
        $fieldList = $this->parseCsv($fields);
        $expandList = $this->parseExpand($expand);
        $basePath = $this->resolveCollectionBasePath($request);

        $payload = $this->presentProduct(
            product: $product,
            profile: $profileName,
            fields: $fieldList,
            expand: $expandList,
            representation: $representation,
            includeLinks: true,
            basePath: $basePath,
        );

        if ($representation === 'ld+json') {
            return $payload;
        }

        return [
            'data' => $payload,
            'meta' => [
                'representation' => 'json',
                'profile' => $profileName,
                'fields' => $fieldList,
                'expand' => $expandList,
            ],
        ];
    }

    public function getShowcaseProfiles(): array
    {
        return [
            [
                'consumer' => 'Frontend',
                'headers' => 'Accept: application/json | X-Response-Profile: minimal',
                'outcome' => 'Slim payload for interactive UI rendering.',
                'href' => '/demo/api/v1/products/wireless-headphones?profile=minimal',
            ],
            [
                'consumer' => 'Crawler',
                'headers' => 'Accept: application/ld+json',
                'outcome' => 'Schema.org product document with semantic fields.',
                'href' => '/demo/api/v1/products/wireless-headphones?format=ld',
            ],
            [
                'consumer' => 'Dashboard',
                'headers' => 'Accept: application/json | expand=category,reviews | X-Response-Profile: full',
                'outcome' => 'Expanded graph for internal admin views.',
                'href' => '/demo/api/v1/products/wireless-headphones?profile=full&expand=category,reviews',
            ],
            [
                'consumer' => 'Search',
                'headers' => 'Accept: application/json | q=headphones',
                'outcome' => 'Filtered collection with pagination metadata.',
                'href' => '/demo/api/v1/products?q=headphones',
            ],
        ];
    }

    public function getContentType(Request $request, ?string $format = null): string
    {
        return $this->resolveRepresentation($request, $format) === 'ld+json'
            ? 'application/ld+json'
            : 'application/json';
    }

    public function buildProductSchema(): array
    {
        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            '$id' => '/demo/api/v1/products/_schema',
            'title' => 'Semitexa Demo Product',
            'type' => 'object',
            'required' => ['slug', 'name', 'price'],
            'properties' => [
                'slug' => [
                    'type' => 'string',
                    'description' => 'Stable product slug used by the demo API detail route.',
                ],
                'name' => [
                    'type' => 'string',
                ],
                'price' => [
                    'type' => 'number',
                    'minimum' => 0,
                ],
                'description' => [
                    'type' => ['string', 'null'],
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['active', 'sale', 'archived'],
                ],
                'category' => [
                    'type' => ['object', 'null'],
                    'required' => ['slug', 'name'],
                    'properties' => [
                        'slug' => ['type' => 'string'],
                        'name' => ['type' => 'string'],
                    ],
                ],
                'rating' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 5,
                ],
                'reviewCount' => [
                    'type' => 'integer',
                    'minimum' => 0,
                ],
                'reviews' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['user', 'rating', 'body'],
                        'properties' => [
                            'user' => ['type' => 'string'],
                            'rating' => ['type' => ['integer', 'null']],
                            'body' => ['type' => ['string', 'null']],
                        ],
                    ],
                ],
                'links' => [
                    'type' => 'object',
                    'properties' => [
                        'self' => ['type' => 'string'],
                        'collection' => ['type' => 'string'],
                    ],
                ],
            ],
        ];
    }

    private function presentProduct(
        DemoProduct $product,
        string $profile,
        array $fields,
        array $expand,
        string $representation,
        bool $includeLinks,
        string $basePath = '/demo/api/v1/products',
    ): array {
        $baseFields = match ($profile) {
            'minimal' => ['slug', 'name', 'price'],
            'full' => ['slug', 'name', 'price', 'description', 'status', 'category', 'rating', 'reviewCount'],
            default => ['slug', 'name', 'price', 'description', 'status', 'category', 'rating'],
        };

        $selected = $fields !== [] ? $fields : $baseFields;
        $slug = $this->slugify($product->getName());
        $category = $this->resolveCategory($product);
        $reviews = in_array('reviews', $expand, true) || $profile === 'full'
            ? $this->reviews->findByProduct($product->getId())
            : [];
        $reviewCount = $reviews !== [] ? count($reviews) : count($this->reviews->findByProduct($product->getId()));
        $rating = $this->resolveRating($reviews !== [] ? $reviews : $this->reviews->findByProduct($product->getId()));

        $json = [];
        foreach ($selected as $field) {
            $json += match ($field) {
                'slug' => ['slug' => $slug],
                'name' => ['name' => $product->getName()],
                'price' => ['price' => (float) $product->getPrice()],
                'description' => ['description' => $product->getDescription()],
                'status' => ['status' => $product->getStatus()],
                'category' => ['category' => $category === null ? null : ['slug' => $category->getSlug(), 'name' => $category->getName()]],
                'rating' => ['rating' => $rating],
                'reviewCount' => ['reviewCount' => $reviewCount],
                default => [],
            };
        }

        if (in_array('reviews', $expand, true) || $profile === 'full') {
            $json['reviews'] = array_map(
                static fn (DemoReview $review): array => [
                    'user' => $review->getUserId(),
                    'rating' => $review->getRating(),
                    'body' => $review->getBody(),
                ],
                array_slice($reviews !== [] ? $reviews : $this->reviews->findByProduct($product->getId()), 0, 4),
            );
        }

        if ($includeLinks) {
            $json['links'] = [
                'self' => $basePath . '/' . $slug,
                'collection' => $basePath,
            ];
        }

        if ($representation === 'ld+json') {
            return [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                '@id' => $basePath . '/' . $slug,
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'category' => $category?->getName(),
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => 'USD',
                    'price' => (float) $product->getPrice(),
                    'availability' => 'https://schema.org/' . ($product->getStatus() === 'active' ? 'InStock' : 'Discontinued'),
                ],
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $rating,
                    'reviewCount' => $reviewCount,
                ],
            ] + ((in_array('reviews', $expand, true) || $profile === 'full')
                ? [
                    'review' => array_map(
                        static fn (array $review): array => [
                            '@type' => 'Review',
                            'author' => $review['user'],
                            'reviewBody' => $review['body'],
                            'reviewRating' => [
                                '@type' => 'Rating',
                                'ratingValue' => $review['rating'],
                            ],
                        ],
                        $json['reviews'] ?? [],
                    ),
                ]
                : []);
        }

        return $json;
    }

    private function resolveRepresentation(Request $request, ?string $format): string
    {
        $format = strtolower(trim((string) $format));
        if (in_array($format, ['ld', 'jsonld', 'ld+json'], true)) {
            return 'ld+json';
        }

        return str_contains(strtolower($request->getHeader('Accept') ?? ''), 'application/ld+json')
            ? 'ld+json'
            : 'json';
    }

    private function resolveProfile(Request $request, ?string $profile): string
    {
        $candidate = strtolower(trim($profile ?: ($request->getHeader('X-Response-Profile') ?? 'standard')));

        return in_array($candidate, ['minimal', 'standard', 'full'], true)
            ? $candidate
            : 'standard';
    }

    private function parseCsv(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $item): string => trim($item),
            explode(',', $value),
        )));
    }

    private function parseExpand(?string $expand): array
    {
        return array_values(array_filter(
            $this->parseCsv($expand),
            static fn (string $item): bool => in_array($item, ['category', 'reviews'], true),
        ));
    }

    private function resolveCategory(DemoProduct $product): ?DemoCategory
    {
        if ($product->getCategoryId() === null || $product->getCategoryId() === '') {
            return null;
        }

        foreach ($this->categories->findAllOrdered() as $category) {
            if ($category->getId() === $product->getCategoryId()) {
                return $category;
            }
        }

        return null;
    }

    /**
     * @param list<DemoReview> $reviews
     */
    private function resolveRating(array $reviews): float
    {
        if ($reviews === []) {
            return 0.0;
        }

        $sum = array_sum(array_map(
            static fn (DemoReview $review): int => $review->getRating() ?? 0,
            $reviews,
        ));

        return round($sum / max(1, count($reviews)), 1);
    }

    private function buildCollectionUrl(
        string $basePath,
        int $page,
        int $limit,
        ?string $query,
        ?string $status,
        string $profile,
        array $fields,
        array $expand,
        ?string $format,
    ): string {
        $params = array_filter([
            'page' => $page > 1 ? (string) $page : null,
            'limit' => $limit !== 8 ? (string) $limit : null,
            'q' => $query,
            'status' => $status,
            'profile' => $profile !== 'standard' ? $profile : null,
            'fields' => $fields !== [] ? implode(',', $fields) : null,
            'expand' => $expand !== [] ? implode(',', $expand) : null,
            'format' => $format,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        return $basePath . ($params === [] ? '' : '?' . http_build_query($params));
    }

    private function resolveCollectionBasePath(Request $request): string
    {
        $path = $request->getPath();

        if (preg_match('#^(/demo/api/v[0-9]+/products)(?:/[^/]+|/_schema)?$#', $path, $matches) === 1) {
            return $matches[1];
        }

        return '/demo/api/v1/products';
    }

    public function findProductBySlug(string $slug): ?DemoProduct
    {
        foreach ($this->products->findPage(200, 0) as $product) {
            if ($this->slugify($product->getName()) === $slug) {
                return $product;
            }
        }

        return null;
    }

    private function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }
}
