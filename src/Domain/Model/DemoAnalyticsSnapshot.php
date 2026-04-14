<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoAnalyticsSnapshot
{
    private string $id = '';
    private ?string $tenantId = null;
    private string $metricType = '';
    private float $value = 0.0;
    private ?\DateTimeImmutable $periodStart = null;
    private ?\DateTimeImmutable $periodEnd = null;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getTenantId(): ?string { return $this->tenantId; }
    public function setTenantId(?string $tenantId): void { $this->tenantId = $tenantId; }

    public function getMetricType(): string { return $this->metricType; }
    public function setMetricType(string $metricType): void { $this->metricType = $metricType; }

    public function getValue(): float { return $this->value; }
    public function setValue(float $value): void { $this->value = $value; }

    public function getPeriodStart(): ?\DateTimeImmutable { return $this->periodStart; }
    public function setPeriodStart(?\DateTimeImmutable $periodStart): void { $this->periodStart = $periodStart; }

    public function getPeriodEnd(): ?\DateTimeImmutable { return $this->periodEnd; }
    public function setPeriodEnd(?\DateTimeImmutable $periodEnd): void { $this->periodEnd = $periodEnd; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
