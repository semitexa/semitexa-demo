<?php

declare(strict_types=1);

namespace App\Catalog\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPayload;

#[AsPayload(
    path: '/search',
    methods: ['GET'],
    responseWith: SearchPageResource::class,
)]
final class SearchPayload
{
    protected string $query = '';

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): void
    {
        $this->query = trim($query);
    }
}
