<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\ReadonlyInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReadonlyInjectionPayload::class, resource: DemoFeatureResource::class)]
final class ReadonlyInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ReadonlyInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'di',
            'readonly',
            'Readonly Injection',
            'One explicit DI path, one shared worker instance — fast at runtime and stable under reload.',
            ['#[InjectAsReadonly]', 'worker-scoped', 'single-path DI', 'reload-stable'],
        );
        $explanation = $this->explanationProvider->getExplanation('di', 'readonly') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'di',
                'currentSlug' => 'readonly',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('di')
            ->withSlug('readonly')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('One explicit DI path, one shared worker instance — fast at runtime and stable under reload.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the injection attribute →')
            ->withDeepDiveLabel('Container tiers explained →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Worker Scope',
                'title' => 'One boot, reused per request',
                'summary' => 'This handler receives readonly services through visible property attributes only. Their object IDs stay stable for the life of the worker.',
                'columns' => ['Service', 'Scope', 'Object ID'],
                'rows' => [
                    [
                        ['text' => 'DemoCatalogService'],
                        ['text' => 'worker'],
                        ['text' => sprintf('#%d', spl_object_id($this->catalog)), 'code' => true],
                    ],
                    [
                        ['text' => 'DemoSourceCodeReader'],
                        ['text' => 'worker'],
                        ['text' => sprintf('#%d', spl_object_id($this->sourceCodeReader)), 'code' => true],
                    ],
                    [
                        ['text' => 'DemoExplanationProvider'],
                        ['text' => 'worker'],
                        ['text' => sprintf('#%d', spl_object_id($this->explanationProvider)), 'code' => true],
                    ],
                ],
                'note' => 'Object IDs stay stable across executions, so readonly services avoid repeated allocation and do not depend on hidden constructor wiring.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
