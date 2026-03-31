<?php

use App\Service\JobService;
use Semitexa\Ssr\Attributes\AsSlotHandler;
use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'imports_dashboard',
    slot: 'job_status',
    deferred: true,
    refreshInterval: 3,
)]
final class ImportJobStatusSlot extends HtmlSlotResponse
{
    public function withStatus(string $status): self
    {
        return $this->with('status', $status);
    }

    public function withProgress(int $progress): self
    {
        return $this->with('progress', $progress);
    }
}

#[AsSlotHandler(slot: ImportJobStatusSlot::class)]
final class ImportJobStatusSlotHandler
{
    public function __construct(
        private readonly JobService $jobs,
    ) {}

    public function handle(ImportJobStatusSlot $slot): ImportJobStatusSlot
    {
        $run = $this->jobs->latestImportRun();

        return $slot
            ->withStatus($run->status())
            ->withProgress($run->progressPercent());
    }
}

// The slot keeps re-rendering server truth. No second client-side state machine is required.
