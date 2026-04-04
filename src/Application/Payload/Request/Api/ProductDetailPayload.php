<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Api\Attributes\ApiVersion;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Graphql\Output\ProductGraphqlView;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;
use Semitexa\Graphql\Attributes\ExposeAsGraphql;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/v1/products/{slug}',
    methods: ['GET'],
    responseWith: DemoApiResponse::class,
    requirements: ['slug' => '[a-z0-9-]+'],
    defaults: ['slug' => 'wireless-headphones'],
)]
#[ExternalApi(version: 'v1', description: 'Demo product detail endpoint')]
#[ApiVersion(version: '1.0.0')]
#[ExposeAsGraphql(
    field: 'productBySlug',
    rootType: 'query',
    output: ProductGraphqlView::class,
    description: 'Derived GraphQL field for loading one product by slug from the Semitexa demo catalog.',
)]
final class ProductDetailPayload
{
    protected ?Request $httpRequest = null;
    protected string $slug = 'wireless-headphones';
    protected ?string $fields = null;
    protected ?string $expand = null;
    protected ?string $profile = null;
    protected ?string $format = null;

    public function getHttpRequest(): ?Request { return $this->httpRequest; }
    public function setHttpRequest(Request $httpRequest): void { $this->httpRequest = $httpRequest; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = trim($slug); }
    public function getFields(): ?string { return $this->fields; }
    public function setFields(?string $fields): void { $this->fields = $fields !== null ? trim($fields) : null; }
    public function getExpand(): ?string { return $this->expand; }
    public function setExpand(?string $expand): void { $this->expand = $expand !== null ? trim($expand) : null; }
    public function getProfile(): ?string { return $this->profile; }
    public function setProfile(?string $profile): void { $this->profile = $profile !== null ? trim($profile) : null; }
    public function getFormat(): ?string { return $this->format; }
    public function setFormat(?string $format): void { $this->format = $format !== null ? trim($format) : null; }
}
