<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoAnalyticsSnapshot
{
    public function __construct(
        public string $id = '',
        public ?string $tenantId = null,
        public string $metricType = '',
        public float $value = 0.0,
        public ?\DateTimeImmutable $periodStart = null,
        public ?\DateTimeImmutable $periodEnd = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
