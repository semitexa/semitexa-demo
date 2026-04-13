<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoProduct
{
    public function __construct(
        public string $id = '',
        public ?string $tenantId = null,
        public string $name = '',
        public ?string $description = null,
        public string $price = '0.00',
        public string $status = 'active',
        public ?string $categoryId = null,
        public ?\DateTimeImmutable $deletedAt = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
