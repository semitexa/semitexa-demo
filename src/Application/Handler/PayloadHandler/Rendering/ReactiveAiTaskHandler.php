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

        $stageNodes = '';
        foreach ($stages as $i => $stageName) {
            $stageResult = is_array($stageResults[$stageName] ?? null) ? $stageResults[$stageName] : [];
            $isDone = ($stageResult['status'] ?? '') === 'done';
            $tokens = $isDone ? (int) ($stageResult['tokens'] ?? 0) : 0;
            $ms = $isDone ? (int) ($stageResult['ms'] ?? 0) : 0;
            $stageNodes .= '<div class="pipeline-stage pipeline-stage--' . ($isDone ? 'done' : 'pending') . '" '
                . 'data-pipeline-stage="' . htmlspecialchars($stageName) . '">'
                . '<div class="pipeline-stage__name">' . htmlspecialchars(ucfirst($stageName)) . '</div>'
                . '<div class="pipeline-stage__detail" data-stage-detail' . ($isDone ? '' : ' hidden') . '>'
                . ($isDone ? $tokens . ' tokens · ' . $ms . 'ms' : '')
                . '</div>'
                . '</div>';
            if ($i < count($stages) - 1) {
                $stageNodes .= '<div class="pipeline-arrow">→</div>';
            }
        }

        $status = $latestTask?->status ?? 'idle';
        $statusClass = match ($status) {
            'running'   => 'badge--active',
            'completed' => 'badge--success',
            'failed'    => 'badge--error',
            default     => 'badge--neutral',
        };

        $stageResultsJson = empty($stageResults) ? '{}' : json_encode($stageResults);

        $resultPreview = '<div class="result-preview">'
            . '<p>Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.</p>'
            . '<div class="pipeline-status-row">'
            . '<span class="badge ' . $statusClass . '" data-pipeline-status>' . htmlspecialchars(ucfirst($status)) . '</span>'
            . ($latestTask !== null ? ' <span class="muted">Task: ' . htmlspecialchars(substr($latestTask->id ?? '', 0, 8)) . '…</span>' : '')
            . '</div>'
            . '<div class="pipeline-stages" data-stage-results-json="' . htmlspecialchars($stageResultsJson) . '">'
            . $stageNodes
            . '</div>'
            . '<p style="margin-top:1rem"><a href="/demo/rendering/reactive-ai/submit" class="btn btn--primary">Submit new task →</a></p>'
            . '</div>';

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
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
