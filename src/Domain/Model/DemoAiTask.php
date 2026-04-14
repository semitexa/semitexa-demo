<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoAiTask
{
    private string $id = '';
    private ?string $tenantId = null;
    private string $inputText = '';
    private string $status = 'pending';
    private ?string $stages = null;
    private ?string $stageResults = null;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getTenantId(): ?string { return $this->tenantId; }
    public function setTenantId(?string $tenantId): void { $this->tenantId = $tenantId; }

    public function getInputText(): string { return $this->inputText; }
    public function setInputText(string $inputText): void { $this->inputText = $inputText; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getStages(): ?string { return $this->stages; }
    public function setStages(?string $stages): void { $this->stages = $stages; }

    public function getStageResults(): ?string { return $this->stageResults; }
    public function setStageResults(?string $stageResults): void { $this->stageResults = $stageResults; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
