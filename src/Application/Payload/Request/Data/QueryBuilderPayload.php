<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/query',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'data',
    title: 'Query Builder',
    slug: 'query',
    summary: 'Compose type-safe queries with a fluent API — no raw SQL, no magic strings.',
    order: 2,
    highlights: ['TableModelQuery', 'where()', 'orderBy()', 'limit()', 'fetchAll()', 'fetchOne()'],
    entryLine: 'Compose type-safe queries with a fluent API — no raw SQL, no magic strings.',
    learnMoreLabel: 'See the query builder →',
    deepDiveLabel: 'How TableModelQuery compiles SQL →',
)]
class QueryBuilderPayload
{
    protected ?string $status = null;
    protected ?float $minPrice = null;
    protected ?float $maxPrice = null;
    protected ?string $orderBy = null;
    protected int $limit = 10;

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status; }

    public function getMinPrice(): ?float { return $this->minPrice; }
    public function setMinPrice(?float $minPrice): void { $this->minPrice = $minPrice !== null ? max(0.0, $minPrice) : null; }

    public function getMaxPrice(): ?float { return $this->maxPrice; }
    public function setMaxPrice(?float $maxPrice): void { $this->maxPrice = $maxPrice !== null ? max(0.0, $maxPrice) : null; }

    public function getOrderBy(): ?string { return $this->orderBy; }
    public function setOrderBy(?string $orderBy): void { $this->orderBy = $orderBy; }

    public function getLimit(): int { return $this->limit; }
    public function setLimit(int $limit): void { $this->limit = max(1, min(50, $limit)); }
}
