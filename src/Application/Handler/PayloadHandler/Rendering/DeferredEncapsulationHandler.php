<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredEncapsulationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredCountdownSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredEncapsulationPayload::class, resource: DemoFeatureResource::class)]
final class DeferredEncapsulationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(DeferredEncapsulationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>Two countdown timers — same slot class, different <code>data-instance</code> IDs. '
            . 'They run completely independently: different durations, separate DOM scope, no shared state.</p>'
            . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1rem">'
            . '<div class="deferred-block deferred-block--countdown">'
            . '<div class="countdown" data-countdown data-duration="30" data-instance="timer-a">'
            . '<span class="countdown__label">Timer A (30s)</span>'
            . '<span class="countdown__display" data-countdown-display>30s</span>'
            . '<div class="countdown__bar"><div class="countdown__progress" data-countdown-progress style="width:100%"></div></div>'
            . '</div></div>'
            . '<div class="deferred-block deferred-block--countdown">'
            . '<div class="countdown" data-countdown data-duration="60" data-instance="timer-b">'
            . '<span class="countdown__label">Timer B (60s)</span>'
            . '<span class="countdown__display" data-countdown-display>60s</span>'
            . '<div class="countdown__bar"><div class="countdown__progress" data-countdown-progress style="width:100%"></div></div>'
            . '</div></div>'
            . '</div>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred-encapsulation') ?? [];

        $sourceCode = [
            'Countdown Slot' => $this->sourceCodeReader->readClassSource(DeferredCountdownSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Block Isolation — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('deferred-encapsulation')
            ->withTitle('Block Isolation')
            ->withSummary('Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.')
            ->withEntryLine('Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.')
            ->withHighlights(['DOM scoping', 'data-instance', 'block isolation', 'independent timers'])
            ->withLearnMoreLabel('See the isolation pattern →')
            ->withDeepDiveLabel('DOM scoping mechanism →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
