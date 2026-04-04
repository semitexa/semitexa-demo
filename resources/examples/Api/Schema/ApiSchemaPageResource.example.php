<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

final class ApiSchemaPageResource
{
    /**
     * @param array<string, mixed> $schema
     */
    public function fromSchema(array $schema): self
    {
        return $this;
    }
}
