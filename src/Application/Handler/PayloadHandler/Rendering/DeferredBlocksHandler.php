<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredBlocksPayload;
use Semitexa\Demo\Application\Resource\Response\DeferredBlocksDemoResource;

#[AsPayloadHandler(payload: DeferredBlocksPayload::class, resource: DeferredBlocksDemoResource::class)]
final class DeferredBlocksHandler implements TypedHandlerInterface
{
    public function handle(DeferredBlocksPayload $payload, DeferredBlocksDemoResource $resource): DeferredBlocksDemoResource
    {
        return $resource
            ->pageTitle('Deferred Blocks — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('deferred')
            ->withTitle('Deferred Blocks')
            ->withSummary('SSR renders the shell first, then expensive regions stream in as real HTML over SSE — no SPA handoff and no client-side page rebuild.')
            ->withEntryLine('The page is usable immediately, and slow regions arrive later as server-rendered HTML instead of hydration-heavy client code.')
            ->withHighlights(['#[AsSlotResource(deferred: true)]', 'skeletonTemplate', 'SSE push', 'SSR-first live UI'])
            ->withLearnMoreLabel('See the deferred grid →')
            ->withDeepDiveLabel('How deferred delivery works →')
            ->withInfoPanel(
                'Deferred slots let the page render immediately while slower regions arrive later as real server-rendered HTML.',
                '#[AsSlotResource(deferred: true)] marks a region for late delivery. The shell is rendered first, then the server streams final slot HTML over SSE.',
                'This keeps the page SSR-first even when some regions are expensive. The browser swaps in HTML instead of rebuilding the page from client state.',
            );
    }
}
