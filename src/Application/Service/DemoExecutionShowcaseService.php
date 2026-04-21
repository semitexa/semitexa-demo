<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
use Semitexa\Ssr\Async\AsyncResourceSseServer;

#[AsService]
final class DemoExecutionShowcaseService
{
    #[InjectAsReadonly]
    protected DemoJobRunRepositoryInterface $jobRunRepository;

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
        $run->setJobType((string) ($meta['job_type'] ?? 'demo_execution_arena_unknown'));
        $run->setStatus('pending');
        $run->setProgressPercent(0);
        $run->setProgressMessage('Ticket issued. Waiting for backend execution.');
        $run->setResultPayload(json_encode([
            'mode' => $mode,
            'mode_label' => $meta['label'] ?? strtoupper($mode),
            'execution_label' => $meta['execution'] ?? 'Unknown',
        ], JSON_THROW_ON_ERROR));

        $run = $this->jobRunRepository->save($run);

        return $run;
    }

    public function getArenaLanes(): array
    {
        $lanes = [];

        foreach (array_keys(self::MODE_META) as $mode) {
            $meta = $this->getModeMeta($mode);
            $latest = $this->jobRunRepository->findByJobType((string) $meta['job_type'])[0] ?? null;
            $resultPayload = $this->decodeJson($latest?->getResultPayload());

            $lanes[] = [
                'mode' => $mode,
                'label' => $meta['label'],
                'execution' => $meta['execution'],
                'responseSummary' => $meta['response_summary'],
                'latestRunId' => $latest?->getId(),
                'latestStatus' => $latest?->getStatus() ?? 'idle',
                'latestProgress' => $latest?->getProgressPercent() ?? 0,
                'latestMessage' => $latest?->getProgressMessage() ?? 'No run yet.',
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
        // Staged timeline: the arena does not do real work. Each step is a fixed
        // delay + canned title that proves the envelope (event → listener → SSE)
        // runs end-to-end. The point of the demo is the *timing shape* of sync
        // vs async vs queued modes, not the work itself.
        $steps = [
            ['progress' => 14, 'title' => 'Step 1/3 — listener accepted the event', 'message' => 'The dispatched domain event has entered the execution pipeline. No real work is running — this timeline is scripted.', 'delayMs' => 650],
            ['progress' => 46, 'title' => 'Step 2/3 — simulated side effect', 'message' => 'The service pauses on purpose so the timing difference between sync/async/queued modes is visible to the viewer.', 'delayMs' => 1150],
            ['progress' => 78, 'title' => 'Step 3/3 — scripted completion marker', 'message' => 'The listener writes a job-run snapshot and prepares the SSE confirmation. The persistence and transport are real; the staged steps are for illustration.', 'delayMs' => 850],
        ];

        $this->setRunState($runId, 'running', 3, 'Backend worker accepted the ticket (scripted demo timeline).');
        $this->emit($sessionId, [
            'event' => 'demo.execution.accepted',
            'run_id' => $runId,
            'mode' => $mode,
            'mode_label' => $meta['label'],
            'worker_model' => $workerModel,
            'status' => 'running',
            'progress' => 3,
            'message' => 'Ticket accepted on the backend (staged timeline — no real work is executed).',
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
            'sync' => 'The response stayed blocked until the scripted timeline completed.',
            'async' => 'The HTTP response returned early; the deferred listener ran the scripted timeline after the page was already free.',
            'queued' => 'The queue worker picked the job up and ran the scripted timeline outside the request lifecycle.',
            default => 'Scripted timeline completed.',
        };

        $this->completeRun($runId, [
            'mode' => $mode,
            'mode_label' => $meta['label'],
            'worker_model' => $workerModel,
            'duration_ms' => $durationMs,
            'completed_at' => $this->timestamp(),
            'summary' => $summary,
        ], 'Backend finished the staged timeline.');

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

        $run->setStatus($status);
        $run->setProgressPercent($progress);
        $run->setProgressMessage($message);
        $this->jobRunRepository->save($run);
    }

    private function completeRun(string $runId, array $resultPayload, string $message): void
    {
        $run = $this->jobRunRepository->findById($runId);
        if ($run === null) {
            return;
        }

        $run->setStatus('completed');
        $run->setProgressPercent(100);
        $run->setProgressMessage($message);
        $run->setResultPayload(json_encode($resultPayload, JSON_THROW_ON_ERROR));
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
