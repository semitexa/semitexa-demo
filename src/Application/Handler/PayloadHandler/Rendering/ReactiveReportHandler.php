<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveReportPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveReportSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoReportBuilder;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReactiveReportPayload::class, resource: DemoFeatureResource::class)]
final class ReactiveReportHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoJobRunRepository $jobRunRepository;

    #[InjectAsReadonly]
    protected DemoReportBuilder $reportBuilder;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(ReactiveReportPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $runs = $this->jobRunRepository->findByJobType('report_generation');
        $latestRun = $runs[0] ?? null;

        $status = $latestRun?->status ?? 'idle';
        $progress = $latestRun?->progress_percent ?? 0;
        $message = $latestRun?->progress_message ?? 'Waiting for next scheduled run…';

        $resultPreview = '<div class="result-preview">'
            . '<p>A scheduled job runs every 30 seconds. The deferred block below polls every 3s and reflects live state.</p>'
            . '<div class="reactive-status-row">'
            . '<span class="badge badge--' . htmlspecialchars($status === 'running' ? 'active' : ($status === 'completed' ? 'success' : 'neutral')) . '">'
            . htmlspecialchars(ucfirst($status))
            . '</span>'
            . '<span class="reactive-progress-label">' . htmlspecialchars($message) . '</span>'
            . '</div>'
            . '<div class="progress-bar" style="margin-top:0.5rem">'
            . '<div class="progress-bar__fill" style="width:' . (int) $progress . '%"></div>'
            . '</div>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-report') ?? [];

        $sourceCode = [
            'ReactiveReportSlot' => $this->sourceCodeReader->readClassSource(ReactiveReportSlot::class),
            'DemoReportBuilder' => $this->sourceCodeReader->readClassSource(DemoReportBuilder::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Reactive Report — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('reactive-report')
            ->withTitle('Reactive Report')
            ->withSummary('A cron job runs every 30s — the deferred block reflects live job state without page reload.')
            ->withEntryLine('A cron job runs every 30s — the deferred block reflects live job state without page reload.')
            ->withHighlights(['refreshInterval', '#[AsScheduledJob]', 'DemoJobRun', 'Pending → Running → chart'])
            ->withLearnMoreLabel('See cron config →')
            ->withDeepDiveLabel('LeaseHeartbeat & retry →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
