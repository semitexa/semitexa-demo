<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;
use Semitexa\Ssr\Async\AsyncResourceSseServer;

#[AsService]
final class DemoExecutionShowcaseService
{
    #[InjectAsReadonly]
    protected DemoJobRunRepository $jobRunRepository;

    private const MODE_META = [
        'sync' => [
            'label' => 'Sync',
            'job_type' => 'demo_execution_arena_sync',
            'execution' => 'Inline request',
            'response_summary' => 'The browser waits because the listener work stays inside the request lifecycle.',
        ],
        'async' => [
            'label' => 'Swoole Async',
            'job_type' => 'demo_execution_arena_async',
            'execution' => 'Post-response defer',
            'response_summary' => 'The response returns first. The deferred listener continues after the request is flushed.',
        ],
        'queued' => [
            'label' => 'Queued Job',
            'job_type' => 'demo_execution_arena_queued',
            'execution' => 'Queue worker pickup',
            'response_summary' => 'The request only emits a durable message. A worker finishes the side effect later.',
        ],
    ];

    public function isSupportedMode(?string $mode): bool
    {
        return is_string($mode) && isset(self::MODE_META[$mode]);
    }

    public function getModeMeta(string $mode): array
    {
        return self::MODE_META[$mode] ?? [];
    }

    public function createRun(string $mode): DemoJobRun
    {
        $meta = $this->getModeMeta($mode);
        $run = new DemoJobRun();
        $run->jobType = (string) ($meta['job_type'] ?? 'demo_execution_arena_unknown');
        $run->status = 'pending';
        $run->progressPercent = 0;
        $run->progressMessage = 'Ticket issued. Waiting for backend execution.';
        $run->resultPayload = json_encode([
            'mode' => $mode,
            'mode_label' => $meta['label'] ?? strtoupper($mode),
            'execution_label' => $meta['execution'] ?? 'Unknown',
        ], JSON_THROW_ON_ERROR);

        $run = $this->jobRunRepository->save($run);

        return $run;
    }

    public function getArenaLanes(): array
    {
        $lanes = [];

        foreach (array_keys(self::MODE_META) as $mode) {
            $meta = $this->getModeMeta($mode);
            $latest = $this->jobRunRepository->findByJobType((string) $meta['job_type'])[0] ?? null;
            $resultPayload = $this->decodeJson($latest?->resultPayload);

            $lanes[] = [
                'mode' => $mode,
                'label' => $meta['label'],
                'execution' => $meta['execution'],
                'responseSummary' => $meta['response_summary'],
                'latestRunId' => $latest?->id,
                'latestStatus' => $latest?->status ?? 'idle',
                'latestProgress' => $latest?->progressPercent ?? 0,
                'latestMessage' => $latest?->progressMessage ?? 'No run yet.',
                'latestFinishedAt' => $resultPayload['completed_at'] ?? null,
                'latestWorker' => $resultPayload['worker_model'] ?? null,
            ];
        }

        return $lanes;
    }

    public function play(string $runId, string $sessionId, string $mode, string $workerModel): void
    {
        $meta = $this->getModeMeta($mode);
        if ($meta === []) {
            return;
        }

        $startedAt = microtime(true);
        $steps = [
            ['progress' => 14, 'title' => 'Intent accepted', 'message' => 'The domain event is alive on the backend and has entered the execution pipeline.', 'delayMs' => 650],
            ['progress' => 46, 'title' => 'Heavy side effect running', 'message' => 'The demo simulates the expensive part of the workflow so the timing difference becomes visible.', 'delayMs' => 1150],
            ['progress' => 78, 'title' => 'Backend artifact sealed', 'message' => 'The worker persists the final snapshot and prepares the SSE confirmation payload.', 'delayMs' => 850],
        ];

        $this->setRunState($runId, 'running', 3, 'Backend worker accepted the ticket.');
        $this->emit($sessionId, [
            'event' => 'demo.execution.accepted',
            'run_id' => $runId,
            'mode' => $mode,
            'mode_label' => $meta['label'],
            'worker_model' => $workerModel,
            'status' => 'running',
            'progress' => 3,
            'message' => 'Ticket accepted on the backend.',
            'sent_at' => $this->timestamp(),
        ]);

        foreach ($steps as $index => $step) {
            $this->sleepMs((int) $step['delayMs']);
            $this->setRunState($runId, 'running', (int) $step['progress'], (string) $step['title']);
            $this->emit($sessionId, [
                'event' => 'demo.execution.progress',
                'run_id' => $runId,
                'mode' => $mode,
                'mode_label' => $meta['label'],
                'worker_model' => $workerModel,
                'status' => 'running',
                'progress' => $step['progress'],
                'step' => $index + 1,
                'title' => $step['title'],
                'message' => $step['message'],
                'sent_at' => $this->timestamp(),
            ]);
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $summary = match ($mode) {
            'sync' => 'The response stayed blocked until all three proof steps completed.',
            'async' => 'The HTTP response returned early; the deferred listener finished after the page was already free.',
            'queued' => 'The queue worker picked the job up and finished it outside the request lifecycle.',
            default => 'Execution completed.',
        };

        $this->completeRun($runId, [
            'mode' => $mode,
            'mode_label' => $meta['label'],
            'worker_model' => $workerModel,
            'duration_ms' => $durationMs,
            'completed_at' => $this->timestamp(),
            'summary' => $summary,
        ], 'Backend finished all proof steps.');

        $this->emit($sessionId, [
            'event' => 'demo.execution.completed',
            'run_id' => $runId,
            'mode' => $mode,
            'mode_label' => $meta['label'],
            'worker_model' => $workerModel,
            'status' => 'completed',
            'progress' => 100,
            'duration_ms' => $durationMs,
            'title' => 'Execution complete',
            'message' => $summary,
            'sent_at' => $this->timestamp(),
        ]);
    }

    private function setRunState(string $runId, string $status, int $progress, string $message): void
    {
        $run = $this->jobRunRepository->findById($runId);
        if ($run === null) {
            return;
        }

        $run->status = $status;
        $run->progressPercent = $progress;
        $run->progressMessage = $message;
        $this->jobRunRepository->save($run);
    }

    private function completeRun(string $runId, array $resultPayload, string $message): void
    {
        $run = $this->jobRunRepository->findById($runId);
        if ($run === null) {
            return;
        }

        $run->status = 'completed';
        $run->progressPercent = 100;
        $run->progressMessage = $message;
        $run->resultPayload = json_encode($resultPayload, JSON_THROW_ON_ERROR);
        $this->jobRunRepository->save($run);
    }

    private function emit(string $sessionId, array $payload): void
    {
        if (trim($sessionId) === '') {
            return;
        }

        AsyncResourceSseServer::deliver($sessionId, $payload);
    }

    private function sleepMs(int $milliseconds): void
    {
        usleep(max(0, $milliseconds) * 1000);
    }

    private function timestamp(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(DATE_ATOM);
    }

    private function decodeJson(?string $json): array
    {
        if (!is_string($json) || trim($json) === '') {
            return [];
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
