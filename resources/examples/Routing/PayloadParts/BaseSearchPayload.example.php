<?php

declare(strict_types=1);

namespace App\Catalog\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Core\Exception\ValidationException;

#[AsPublicPayload(
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
        $query = trim($query);

        if ($query === '') {
            throw new ValidationException(['query' => ['Search query is required.']]);
        }

        if (strlen($query) > 120) {
            throw new ValidationException(['query' => ['Search query must stay below 120 characters.']]);
        }

        $this->query = $query;
    }
}
