<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/products',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
)]
class OrmCrudPayload
{
    protected ?Request $httpRequest = null;
    protected ?string $action = null;
    protected ?string $productId = null;
    protected ?string $name = null;
    protected ?float $price = null;
    protected ?string $status = null;

    public function getAction(): ?string { return $this->action; }
    public function setAction(?string $action): void { $this->action = $action; }

    public function getHttpRequest(): ?Request { return $this->httpRequest; }
    public function setHttpRequest(Request $httpRequest): void { $this->httpRequest = $httpRequest; }

    public function getProductId(): ?string { return $this->productId; }
    public function setProductId(?string $productId): void { $this->productId = $productId; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): void { $this->name = $name; }

    public function getPrice(): ?float { return $this->price; }
    public function setPrice(?float $price): void { $this->price = $price !== null ? max(0.0, $price) : null; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status; }
}
