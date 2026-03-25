<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/products',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'data',
    title: 'ORM CRUD',
    slug: 'products',
    summary: 'Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.',
    order: 1,
    highlights: ['#[FromTable]', '#[Column]', 'HasUuidV7', 'HasTimestamps', 'SoftDeletes', 'AbstractRepository'],
    entryLine: 'Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.',
    learnMoreLabel: 'See the model & repository →',
    deepDiveLabel: 'How the ORM maps resources →',
)]
class OrmCrudPayload
{
    protected ?string $action = null;
    protected ?string $productId = null;
    protected ?string $name = null;
    protected ?float $price = null;
    protected ?string $status = null;

    public function getAction(): ?string { return $this->action; }
    public function setAction(?string $action): void { $this->action = $action; }

    public function getProductId(): ?string { return $this->productId; }
    public function setProductId(?string $productId): void { $this->productId = $productId; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): void { $this->name = $name; }

    public function getPrice(): ?float { return $this->price; }
    public function setPrice(?float $price): void { $this->price = $price; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status; }
}
