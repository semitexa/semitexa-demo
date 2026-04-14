<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoJobRun
{
    private string $id = '';
    private string $jobType = '';
    private ?string $schedulerRunId = null;
    private string $status = 'pending';
    private int $progressPercent = 0;
    private ?string $progressMessage = null;
    private ?string $resultPayload = null;
    private int $attemptNumber = 1;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getJobType(): string { return $this->jobType; }
    public function setJobType(string $jobType): void { $this->jobType = $jobType; }

    public function getSchedulerRunId(): ?string { return $this->schedulerRunId; }
    public function setSchedulerRunId(?string $schedulerRunId): void { $this->schedulerRunId = $schedulerRunId; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getProgressPercent(): int { return $this->progressPercent; }
    public function setProgressPercent(int $progressPercent): void { $this->progressPercent = $progressPercent; }

    public function getProgressMessage(): ?string { return $this->progressMessage; }
    public function setProgressMessage(?string $progressMessage): void { $this->progressMessage = $progressMessage; }

    public function getResultPayload(): ?string { return $this->resultPayload; }
    public function setResultPayload(?string $resultPayload): void { $this->resultPayload = $resultPayload; }

    public function getAttemptNumber(): int { return $this->attemptNumber; }
    public function setAttemptNumber(int $attemptNumber): void { $this->attemptNumber = $attemptNumber; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
}
