<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredEncapsulationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredCountdownSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredEncapsulationPayload::class, resource: DemoFeatureResource::class)]
final class DeferredEncapsulationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(DeferredEncapsulationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'deferred-encapsulation',
            entryLine: 'Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.',
            learnMoreLabel: 'See the isolation pattern →',
            deepDiveLabel: 'DOM scoping mechanism →',
            relatedSlugs: [],
            fallbackTitle: 'Block Isolation',
            fallbackSummary: 'Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.',
            fallbackHighlights: ['DOM scoping', 'data-instance', 'block isolation', 'independent timers'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'deferred-encapsulation') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Countdown Slot' => $this->sourceCodeReader->readClassSource(DeferredCountdownSlot::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/countdown-comparison.html.twig', [
                'eyebrow' => 'Scoped Instances',
                'title' => 'Same slot class, separate runtime state',
                'summary' => 'Two countdown blocks reuse the same widget template and client module, but each instance keeps its own timer and DOM scope.',
                'timers' => [
                    ['label' => 'Timer A', 'duration' => 30, 'instanceId' => 'timer-a'],
                    ['label' => 'Timer B', 'duration' => 60, 'instanceId' => 'timer-b'],
                ],
            ]);
    }
}
