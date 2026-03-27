<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Api\Attributes\ApiVersion;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/v1/products',
    methods: ['GET'],
    responseWith: DemoApiResponse::class,
)]
#[ExternalApi(version: 'v1', description: 'Demo product collection endpoint')]
#[ApiVersion(version: '1.0.0')]
final class ProductListPayload
{
    protected ?Request $httpRequest = null;
    protected ?string $q = null;
    protected ?string $status = null;
    protected ?string $fields = null;
    protected ?string $expand = null;
    protected ?string $profile = null;
    protected ?string $format = null;
    protected int $page = 1;
    protected int $limit = 8;

    public function getHttpRequest(): ?Request { return $this->httpRequest; }
    public function setHttpRequest(Request $httpRequest): void { $this->httpRequest = $httpRequest; }
    public function getQ(): ?string { return $this->q; }
    public function setQ(?string $q): void { $this->q = $q !== null ? trim($q) : null; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(?string $status): void { $this->status = $status !== null ? trim($status) : null; }
    public function getFields(): ?string { return $this->fields; }
    public function setFields(?string $fields): void { $this->fields = $fields !== null ? trim($fields) : null; }
    public function getExpand(): ?string { return $this->expand; }
    public function setExpand(?string $expand): void { $this->expand = $expand !== null ? trim($expand) : null; }
    public function getProfile(): ?string { return $this->profile; }
    public function setProfile(?string $profile): void { $this->profile = $profile !== null ? trim($profile) : null; }
    public function getFormat(): ?string { return $this->format; }
    public function setFormat(?string $format): void { $this->format = $format !== null ? trim($format) : null; }
    public function getPage(): int { return $this->page; }
    public function setPage(int|string|null $page): void { $this->page = max(1, (int) ($page ?? 1)); }
    public function getLimit(): int { return $this->limit; }
    public function setLimit(int|string|null $limit): void { $this->limit = min(24, max(1, (int) ($limit ?? 8))); }
}
