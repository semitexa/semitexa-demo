<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoReview
{
    private string $id = '';
    private ?string $tenantId = null;
    private string $productId = '';
    private string $userId = '';
    private ?int $rating = null;
    private ?string $body = null;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getTenantId(): ?string { return $this->tenantId; }
    public function setTenantId(?string $tenantId): void { $this->tenantId = $tenantId; }

    public function getProductId(): string { return $this->productId; }
    public function setProductId(string $productId): void { $this->productId = $productId; }

    public function getUserId(): string { return $this->userId; }
    public function setUserId(string $userId): void { $this->userId = $userId; }

    public function getRating(): ?int { return $this->rating; }
    public function setRating(?int $rating): void { $this->rating = $rating; }

    public function getBody(): ?string { return $this->body; }
    public function setBody(?string $body): void { $this->body = $body; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
