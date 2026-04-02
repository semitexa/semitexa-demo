<?php

declare(strict_types=1);

namespace Examples\Rendering\Philosophy\Support;

final class JobService
{
    public function latestImportRun(): ImportRun
    {
        return new ImportRun('queued', 0);
    }
}

final class ImportRun
{
    public function __construct(
        private string $status,
        private int $progressPercent,
    ) {}

    public function status(): string
    {
        return $this->status;
    }

    public function progressPercent(): int
    {
        return $this->progressPercent;
    }
}
