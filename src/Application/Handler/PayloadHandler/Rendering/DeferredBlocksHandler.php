<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredBlocksPayload;
use Semitexa\Demo\Application\Resource\Response\DeferredBlocksDemoResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredProductCarouselSlot;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredReviewFeedSlot;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredBlocksPayload::class, resource: DeferredBlocksDemoResource::class)]
final class DeferredBlocksHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DeferredBlocksPayload $payload, DeferredBlocksDemoResource $resource): DeferredBlocksDemoResource
    {
        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred') ?? [];

        $sourceCode = [
            'Carousel Slot' => $this->sourceCodeReader->readClassSource(DeferredProductCarouselSlot::class),
            'Review Feed Slot' => $this->sourceCodeReader->readClassSource(DeferredReviewFeedSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Deferred Blocks — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'deferred',
                'infoWhat' => $explanation['what'] ?? 'Render the page shell first, then stream expensive blocks in after the response.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('deferred')
            ->withTitle('Deferred Blocks')
            ->withEntryLine('Render the page shell instantly — deferred slots stream in over SSE as they complete.')
            ->withHighlights(['#[AsSlotResource(deferred: true)]', 'skeletonTemplate', 'SSE push', 'skeleton → content'])
            ->withLearnMoreLabel('How it works →')
            ->withDeepDiveLabel('SSE push mechanism →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
