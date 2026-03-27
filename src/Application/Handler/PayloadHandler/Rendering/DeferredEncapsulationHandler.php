<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredEncapsulationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredCountdownSlot;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredEncapsulationPayload::class, resource: DemoFeatureResource::class)]
final class DeferredEncapsulationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DeferredEncapsulationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred-encapsulation') ?? [];

        $sourceCode = [
            'Countdown Slot' => $this->sourceCodeReader->readClassSource(DeferredCountdownSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Block Isolation — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'deferred-encapsulation',
                'infoWhat' => $explanation['what'] ?? 'Repeated deferred blocks stay isolated through per-instance data attributes and scoped DOM.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('deferred-encapsulation')
            ->withTitle('Block Isolation')
            ->withSummary('Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.')
            ->withEntryLine('Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.')
            ->withHighlights(['DOM scoping', 'data-instance', 'block isolation', 'independent timers'])
            ->withLearnMoreLabel('See the isolation pattern →')
            ->withDeepDiveLabel('DOM scoping mechanism →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/countdown-comparison.html.twig', [
                'eyebrow' => 'Scoped Instances',
                'title' => 'Same slot class, separate runtime state',
                'summary' => 'Two countdown blocks render from the same slot resource, but each instance keeps its own timer and DOM scope.',
                'timers' => [
                    ['label' => 'Timer A', 'display' => '30s', 'caption' => 'Duration', 'progress' => 100],
                    ['label' => 'Timer B', 'display' => '60s', 'caption' => 'Duration', 'progress' => 100],
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
