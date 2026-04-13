<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoReview
{
    public function __construct(
        public string $id = '',
        public ?string $tenantId = null,
        public string $productId = '',
        public string $userId = '',
        public ?int $rating = null,
        public ?string $body = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
