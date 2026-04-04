<?php

declare(strict_types=1);

namespace App\Application\Resource\Api;

final class ProductSchemaResource
{
    /**
     * @param array<string, mixed> $schema
     */
    public function fromSchema(array $schema): self
    {
        return $this;
    }
}
