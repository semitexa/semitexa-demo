<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoProduct
{
    private string $id = '';
    private ?string $tenantId = null;
    private string $name = '';
    private ?string $description = null;
    private string $price = '0.00';
    private string $status = 'active';
    private ?string $categoryId = null;
    private ?\DateTimeImmutable $deletedAt = null;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getTenantId(): ?string { return $this->tenantId; }
    public function setTenantId(?string $tenantId): void { $this->tenantId = $tenantId; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }

    public function getPrice(): string { return $this->price; }
    public function setPrice(string $price): void { $this->price = $price; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getCategoryId(): ?string { return $this->categoryId; }
    public function setCategoryId(?string $categoryId): void { $this->categoryId = $categoryId; }

    public function getDeletedAt(): ?\DateTimeImmutable { return $this->deletedAt; }
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): void { $this->deletedAt = $deletedAt; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
