<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Container\ReadonlyInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReadonlyInjectionPayload::class, resource: DemoFeatureResource::class)]
final class ReadonlyInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    // Catalog is kept as a direct injection on this handler specifically: the preview
    // demonstrates worker-scoped object identity by displaying spl_object_id() for each
    // injected readonly service, which needs direct references to them.
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ReadonlyInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'di',
            slug: 'readonly',
            entryLine: 'One explicit DI path, one shared worker instance — fast at runtime and stable under reload.',
            learnMoreLabel: 'See the injection attribute →',
            deepDiveLabel: 'Container tiers explained →',
            relatedSlugs: [],
            fallbackTitle: 'Readonly Injection',
            fallbackSummary: 'One explicit DI path, one shared worker instance — fast at runtime and stable under reload.',
            fallbackHighlights: ['#[InjectAsReadonly]', 'worker-scoped', 'single-path DI', 'reload-stable'],
            explanation: $this->explanationProvider->getExplanation('di', 'readonly') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Worker Scope',
                'title' => 'One boot, reused per request',
                'summary' => 'This handler receives readonly services through visible property attributes only. Their object IDs stay stable for the life of the worker.',
                'columns' => ['Service', 'Scope', 'Object ID'],
                'rows' => [
                    [['text' => 'DemoCatalogService'], ['text' => 'worker'], ['text' => sprintf('#%d', spl_object_id($this->catalog)), 'code' => true]],
                    [['text' => 'DemoSourceCodeReader'], ['text' => 'worker'], ['text' => sprintf('#%d', spl_object_id($this->sourceCodeReader)), 'code' => true]],
                    [['text' => 'DemoExplanationProvider'], ['text' => 'worker'], ['text' => sprintf('#%d', spl_object_id($this->explanationProvider)), 'code' => true]],
                ],
                'note' => 'Object IDs stay stable across executions, so readonly services avoid repeated allocation and do not depend on hidden constructor wiring.',
            ]);
    }
}
