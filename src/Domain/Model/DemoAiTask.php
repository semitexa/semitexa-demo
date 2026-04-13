<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

class DemoAiTask
{
    public function __construct(
        public string $id = '',
        public ?string $tenantId = null,
        public string $inputText = '',
        public string $status = 'pending',
        public ?string $stages = null,
        public ?string $stageResults = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}
}
