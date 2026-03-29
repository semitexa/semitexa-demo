<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoAiTaskRepository;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveAiTaskPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveAiTaskSlot;
use Semitexa\Demo\Application\Service\DemoAiTextProcessor;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReactiveAiTaskPayload::class, resource: DemoFeatureResource::class)]
final class ReactiveAiTaskHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoAiTaskRepository $aiTaskRepository;

    #[InjectAsReadonly]
    protected DemoAiTextProcessor $aiTextProcessor;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(ReactiveAiTaskPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $stages = $this->aiTextProcessor->getStages();
        $recentTasks = $this->aiTaskRepository->findByStatus('running');
        if (empty($recentTasks)) {
            $recentTasks = $this->aiTaskRepository->findByStatus('pending');
        }
        if (empty($recentTasks)) {
            $recentTasks = $this->aiTaskRepository->findByStatus('failed');
        }
        if (empty($recentTasks)) {
            $recentTasks = $this->aiTaskRepository->findByStatus('completed');
        }

        $latestTask = $recentTasks[0] ?? null;
        $stageResults = [];
        if ($latestTask !== null && !empty($latestTask->stage_results)) {
            try {
                $stageResults = json_decode($latestTask->stage_results, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $stageResults = [];
            }
        }

        $stageRows = [];
        foreach ($stages as $stageName) {
            $stageResult = is_array($stageResults[$stageName] ?? null) ? $stageResults[$stageName] : [];
            $isDone = ($stageResult['status'] ?? '') === 'done';
            $tokens = $isDone ? (int) ($stageResult['tokens'] ?? 0) : 0;
            $ms = $isDone ? (int) ($stageResult['ms'] ?? 0) : 0;
            $stageRows[] = [
                'key' => $stageName,
                'label' => ucfirst($stageName),
                'done' => $isDone,
                'tokens' => $tokens,
                'ms' => $ms,
            ];
        }

        $status = $latestTask?->status ?? 'idle';
        $statusClass = match ($status) {
            'running'   => 'badge--active',
            'completed' => 'badge--success',
            'failed'    => 'badge--error',
            default     => 'badge--neutral',
        };

        $stageResultsJson = empty($stageResults) ? '{}' : (json_encode($stageResults) ?: '{}');

        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-ai') ?? [];

        $sourceCode = [
            'ReactiveAiTaskSlot' => $this->sourceCodeReader->readClassSource(ReactiveAiTaskSlot::class),
            'DemoAiTextProcessor' => $this->sourceCodeReader->readClassSource(DemoAiTextProcessor::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Reactive AI Task — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('reactive-ai')
            ->withTitle('Reactive AI Task')
            ->withSummary('Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.')
            ->withEntryLine('Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.')
            ->withHighlights(['DemoAiTask', 'stage-by-stage', 'refreshInterval: 2', 'user-triggered → cron pickup'])
            ->withLearnMoreLabel('See submit form →')
            ->withDeepDiveLabel('Processor architecture →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/ai-pipeline.html.twig', [
                'summary' => 'Submit a task and watch each processing stage reveal itself as background work completes.',
                'statusVariant' => str_replace('badge--', '', $statusClass),
                'statusLabel' => ucfirst($status),
                'taskLabel' => $latestTask !== null ? 'Task ' . substr((string) ($latestTask->id ?? ''), 0, 8) . '…' : null,
                'stages' => $stageRows,
                'stageResultsJson' => $stageResultsJson,
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
