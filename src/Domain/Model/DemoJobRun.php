<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoJobRun
{
    public function __construct(
        public string $id = '',
        public string $jobType = '',
        public ?string $schedulerRunId = null,
        public string $status = 'pending',
        public int $progressPercent = 0,
        public ?string $progressMessage = null,
        public ?string $resultPayload = null,
        public int $attemptNumber = 1,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
