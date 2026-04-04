<?php

declare(strict_types=1);

namespace App\Domain\Api;

interface ProductApiReaderInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listCurrentVersion(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForApi(): array;
}
