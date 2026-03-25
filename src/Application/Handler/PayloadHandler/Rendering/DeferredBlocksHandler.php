<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredBlocksPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredProductCarouselSlot;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredReviewFeedSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredBlocksPayload::class, resource: DemoFeatureResource::class)]
final class DeferredBlocksHandler implements TypedHandlerInterface
{
    private const BLOCKS = [
        ['name' => 'Product Carousel',  'slot' => 'deferred_product_carousel', 'delay' => '~120ms'],
        ['name' => 'Review Feed',       'slot' => 'deferred_review_feed',       'delay' => '~80ms'],
        ['name' => 'Chart Widget',      'slot' => 'deferred_chart_widget',      'delay' => '~200ms'],
        ['name' => 'Search Filters',    'slot' => 'deferred_search_filter',     'delay' => '~60ms'],
        ['name' => 'Notification Bell', 'slot' => 'deferred_notification',      'delay' => '~40ms'],
        ['name' => 'Countdown Timer',   'slot' => 'deferred_countdown',         'delay' => '~30ms'],
    ];

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(DeferredBlocksPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $rows = '';
        foreach (self::BLOCKS as $block) {
            $rows .= sprintf(
                '<tr><td>%s</td><td><code>%s</code></td><td>%s</td><td><span class="badge badge--active">streaming</span></td></tr>',
                htmlspecialchars($block['name']),
                htmlspecialchars($block['slot']),
                htmlspecialchars($block['delay']),
            );
        }

        $resultPreview = '<div class="result-preview">'
            . '<p>The page shell renders in <strong>&lt;5ms</strong>. '
            . 'All 6 deferred blocks stream in via SSE — each shows its skeleton until content arrives.</p>'
            . '<div class="deferred-timeline" id="deferred-timeline" data-timeline>'
            . '<div class="deferred-timeline__bar"><div class="deferred-timeline__fill" style="width:0%"></div></div>'
            . '<div class="deferred-timeline__label">Waiting for blocks…</div>'
            . '</div>'
            . '<table class="data-table" style="margin-top:1rem">'
            . '<thead><tr><th>Block</th><th>Slot</th><th>Typical arrival</th><th>Status</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred') ?? [];

        $sourceCode = [
            'Carousel Slot' => $this->sourceCodeReader->readClassSource(DeferredProductCarouselSlot::class),
            'Review Feed Slot' => $this->sourceCodeReader->readClassSource(DeferredReviewFeedSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Deferred Blocks — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('deferred')
            ->withTitle('Deferred Blocks')
            ->withSummary('Render the page shell instantly — deferred slots stream in over SSE as they complete.')
            ->withEntryLine('Render the page shell instantly — deferred slots stream in over SSE as they complete.')
            ->withHighlights(['#[AsSlotResource(deferred: true)]', 'skeletonTemplate', 'SSE push', 'skeleton → content'])
            ->withLearnMoreLabel('See all 6 deferred blocks →')
            ->withDeepDiveLabel('SSE push mechanism →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
