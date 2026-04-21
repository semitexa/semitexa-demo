<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Container\DiOverviewPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DiOverviewPayload::class, resource: DemoFeatureResource::class)]
final class DiOverviewHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(DiOverviewPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'di',
            slug: 'overview',
            entryLine: 'One canonical DI path for container-managed classes: explicit properties, explicit lifecycles, deterministic boot.',
            learnMoreLabel: 'See the Semitexa canon →',
            deepDiveLabel: 'Why mixed DI fails →',
            relatedSlugs: [],
            fallbackTitle: 'DI Canon',
            fallbackSummary: 'One canonical DI path for container-managed classes: explicit properties, explicit lifecycles, deterministic boot.',
            fallbackHighlights: ['single-path DI', '#[InjectAsReadonly]', '#[InjectAsMutable]', 'boot-time validation'],
            explanation: $this->explanationProvider->getExplanation('di', 'overview') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Payload' => $this->sourceCodeReader->readClassSource(DiOverviewPayload::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Semitexa Canon',
                'title' => 'One path per concern',
                'summary' => 'The container-managed model is deliberately narrow so boot, tooling, and reload behavior stay deterministic even after large cross-package changes.',
                'columns' => ['Concern', 'Canonical path', 'Why it exists'],
                'rows' => [
                    [['text' => 'Service dependency'], ['text' => '#[InjectAsReadonly] or #[InjectAsMutable]', 'code' => true], ['text' => 'A dependency is visible where the class uses it.']],
                    [['text' => 'Scalar config'], ['text' => '#[Config]', 'code' => true], ['text' => 'Configuration stops leaking in through constructor arguments or magic env reads — values arrive on typed #[Config] properties instead.']],
                    [['text' => 'Lifecycle'], ['text' => 'worker-shared or execution-scoped'], ['text' => 'State boundaries stay explicit in a long-running worker model.']],
                    [['text' => 'Variant selection'], ['text' => 'contract metadata or closed-world factory'], ['text' => 'Selection stays reviewable instead of becoming runtime magic.']],
                ],
                'paragraphs' => [
                    'Semitexa does not optimize for infinite DI flexibility. It optimizes for clarity under change.',
                    'That tradeoff matters because the same application must survive worker reuse, graceful reload, static analysis, and large LLM-assisted refactors without hidden dependency channels.',
                ],
                'note' => 'The rest of the DI section shows each piece of this canon in isolation: readonly services, execution scope, factories, and contracts.',
            ]);
    }
}
