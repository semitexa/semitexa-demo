<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/filtering',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'data',
    title: 'Filtering',
    slug: 'filtering',
    summary: 'Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.',
    order: 10,
    highlights: ['#[Filterable]', 'FilterableTrait', 'FilterableResourceInterface', 'getFilterCriteria()'],
    entryLine: 'Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.',
    learnMoreLabel: 'See the filter attributes →',
    deepDiveLabel: 'How filter criteria compile →',
)]
class FilteringPayload
{
    protected ?string $name = null;
    protected ?float $priceMin = null;
    protected ?float $priceMax = null;
    protected ?string $status = null;
    protected ?string $categoryId = null;

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): void { $this->name = $name; }

    public function getPriceMin(): ?float { return $this->priceMin; }
    public function setPriceMin(?float $priceMin): void { $this->priceMin = $priceMin; }

    public function getPriceMax(): ?float { return $this->priceMax; }
    public function setPriceMax(?float $priceMax): void { $this->priceMax = $priceMax; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status; }

    public function getCategoryId(): ?string { return $this->categoryId; }
    public function setCategoryId(?string $categoryId): void { $this->categoryId = $categoryId; }
}
