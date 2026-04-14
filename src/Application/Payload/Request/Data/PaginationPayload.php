<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/pagination',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
class PaginationPayload
{
    protected int $page = 1;
    protected int $limit = 10;
    protected string $mode = 'offset';

    public function getPage(): int { return $this->page; }
    public function setPage(int $page): void { $this->page = max(1, $page); }

    public function getLimit(): int { return $this->limit; }
    public function setLimit(int $limit): void { $this->limit = max(1, min(50, $limit)); }

    public function getMode(): string { return $this->mode; }
    public function setMode(string $mode): void { $this->mode = in_array($mode, ['offset', 'cursor'], true) ? $mode : 'offset'; }
}
