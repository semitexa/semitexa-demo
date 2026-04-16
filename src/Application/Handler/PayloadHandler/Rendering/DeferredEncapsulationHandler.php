<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredEncapsulationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredCountdownSlot;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredEncapsulationPayload::class, resource: DemoFeatureResource::class)]
final class DeferredEncapsulationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DeferredEncapsulationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'rendering',
            'deferred-encapsulation',
            'Block Isolation',
            'Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.',
            ['DOM scoping', 'data-instance', 'block isolation', 'independent timers'],
        );
        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred-encapsulation') ?? [];

        $sourceCode = [
            'Countdown Slot' => $this->sourceCodeReader->readClassSource(DeferredCountdownSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'deferred-encapsulation',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('deferred-encapsulation')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the isolation pattern →')
            ->withDeepDiveLabel('DOM scoping mechanism →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/countdown-comparison.html.twig', [
                'eyebrow' => 'Scoped Instances',
                'title' => 'Same slot class, separate runtime state',
                'summary' => 'Two countdown blocks reuse the same widget template and client module, but each instance keeps its own timer and DOM scope.',
                'timers' => [
                    ['label' => 'Timer A', 'duration' => 30, 'instanceId' => 'timer-a'],
                    ['label' => 'Timer B', 'duration' => 60, 'instanceId' => 'timer-b'],
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
