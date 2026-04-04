<?php

declare(strict_types=1);

namespace App\Application\Resource\Api;

final class ProductSchemaResource
{
    /**
     * @var array<string, mixed>
     */
    private array $schema = [];

    /**
     * @param array<string, mixed> $schema
     */
    public function fromSchema(array $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSchema(): array
    {
        return $this->schema;
    }
}
