<?php

declare(strict_types=1);

namespace Examples\Shared;

interface CatalogReadRepositoryInterface
{
    public function findBySlug(string $slug): ?object;
}
