<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoOrder
{
    private string $id = '';
    private ?string $tenantId = null;
    private string $userId = '';
    private string $status = 'pending';
    private string $totalAmount = '0.00';
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getTenantId(): ?string { return $this->tenantId; }
    public function setTenantId(?string $tenantId): void { $this->tenantId = $tenantId; }

    public function getUserId(): string { return $this->userId; }
    public function setUserId(string $userId): void { $this->userId = $userId; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getTotalAmount(): string { return $this->totalAmount; }
    public function setTotalAmount(string $totalAmount): void { $this->totalAmount = $totalAmount; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
