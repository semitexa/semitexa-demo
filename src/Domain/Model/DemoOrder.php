<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoOrder
{
    public function __construct(
        public string $id = '',
        public ?string $tenantId = null,
        public string $userId = '',
        public string $status = 'pending',
        public string $totalAmount = '0.00',
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
