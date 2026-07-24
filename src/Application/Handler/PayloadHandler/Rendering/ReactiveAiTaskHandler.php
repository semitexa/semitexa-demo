<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Domain\Repository\DemoAiTaskRepositoryInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
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
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoAiTaskRepositoryInterface $aiTaskRepository;

    #[InjectAsReadonly]
    protected DemoAiTextProcessor $aiTextProcessor;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ReactiveAiTaskPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'reactive-ai',
            entryLine: 'Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.',
            learnMoreLabel: 'See submit form →',
            deepDiveLabel: 'Processor architecture →',
            relatedSlugs: [],
            fallbackTitle: 'Reactive AI Task',
            fallbackSummary: 'Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.',
            fallbackHighlights: ['DemoAiTask', 'stage-by-stage', 'refreshInterval: 2', 'user-triggered → cron pickup'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'reactive-ai') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $latestTask = $this->findLatestTask();
        $stageResults = $this->decodeStageResults($latestTask?->getStageResults());
        $status = $latestTask?->getStatus() ?? 'idle';
        $statusVariant = match ($status) {
            'running'   => 'active',
            'completed' => 'success',
            'failed'    => 'error',
            default     => 'neutral',
        };

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'ReactiveAiTaskSlot' => $this->sourceCodeReader->readClassSource(ReactiveAiTaskSlot::class),
                'DemoAiTextProcessor' => $this->sourceCodeReader->readClassSource(DemoAiTextProcessor::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/ai-pipeline.html.twig', [
                'summary' => 'Submit a task and watch each processing stage reveal itself as background work completes.',
                'statusVariant' => $statusVariant,
                'statusLabel' => ucfirst($status),
                'taskLabel' => $latestTask !== null ? 'Task ' . substr($latestTask->getId(), 0, 8) . '…' : null,
                'stages' => $this->buildStageRows($stageResults),
                'stageResultsJson' => $stageResults === [] ? '{}' : (json_encode($stageResults) ?: '{}'),
            ]);
    }

    private function findLatestTask(): ?\Semitexa\Demo\Domain\Model\DemoAiTask
    {
        foreach (['running', 'pending', 'failed', 'completed'] as $status) {
            $tasks = $this->aiTaskRepository->findByStatus($status);
            if (!empty($tasks)) {
                return $tasks[0];
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeStageResults(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $stageResults
     * @return list<array{key: string, label: string, done: bool, tokens: int, ms: int}>
     */
    private function buildStageRows(array $stageResults): array
    {
        $rows = [];

        foreach ($this->aiTextProcessor->getStages() as $stageName) {
            $stage = is_array($stageResults[$stageName] ?? null) ? $stageResults[$stageName] : [];
            $isDone = ($stage['status'] ?? '') === 'done';

            $rows[] = [
                'key' => $stageName,
                'label' => ucfirst($stageName),
                'done' => $isDone,
                'tokens' => $isDone ? (int) ($stage['tokens'] ?? 0) : 0,
                'ms' => $isDone ? (int) ($stage['ms'] ?? 0) : 0,
            ];
        }

        return $rows;
    }
}
