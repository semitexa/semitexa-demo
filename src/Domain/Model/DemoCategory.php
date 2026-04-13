<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoCategory
{
    public function __construct(
        public string $id = '',
        public string $name = '',
        public string $slug = '',
        public ?string $description = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
