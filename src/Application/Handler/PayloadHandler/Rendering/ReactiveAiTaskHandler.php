<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Domain\Repository\DemoAiTaskRepositoryInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveAiTaskPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveAiTaskSlot;
use Semitexa\Demo\Application\Service\DemoAiTextProcessor;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReactiveAiTaskPayload::class, resource: DemoFeatureResource::class)]
final class ReactiveAiTaskHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoAiTaskRepositoryInterface $aiTaskRepository;

    #[InjectAsReadonly]
    protected DemoAiTextProcessor $aiTextProcessor;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

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
        if ($latestTask !== null && !empty($latestTask->getStageResults())) {
            try {
                $stageResults = json_decode($latestTask->getStageResults(), true, 512, JSON_THROW_ON_ERROR);
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

        $status = $latestTask?->getStatus() ?? 'idle';
        $statusClass = match ($status) {
            'running'   => 'badge--active',
            'completed' => 'badge--success',
            'failed'    => 'badge--error',
            default     => 'badge--neutral',
        };

        $stageResultsJson = empty($stageResults) ? '{}' : (json_encode($stageResults) ?: '{}');

        $presentation = $this->documents->resolve(
            'rendering',
            'reactive-ai',
            'Reactive AI Task',
            'Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.',
            ['DemoAiTask', 'stage-by-stage', 'refreshInterval: 2', 'user-triggered → cron pickup'],
        );
        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-ai') ?? [];

        $sourceCode = [
            'ReactiveAiTaskSlot' => $this->sourceCodeReader->readClassSource(ReactiveAiTaskSlot::class),
            'DemoAiTextProcessor' => $this->sourceCodeReader->readClassSource(DemoAiTextProcessor::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'reactive-ai',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('reactive-ai')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See submit form →')
            ->withDeepDiveLabel('Processor architecture →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/ai-pipeline.html.twig', [
                'summary' => 'Submit a task and watch each processing stage reveal itself as background work completes.',
                'statusVariant' => str_replace('badge--', '', $statusClass),
                'statusLabel' => ucfirst($status),
                'taskLabel' => $latestTask !== null ? 'Task ' . substr($latestTask->getId(), 0, 8) . '…' : null,
                'stages' => $stageRows,
                'stageResultsJson' => $stageResultsJson,
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
