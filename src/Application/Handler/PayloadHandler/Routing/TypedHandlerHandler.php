<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\TypedHandlerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: TypedHandlerPayload::class, resource: DemoFeatureResource::class)]
final class TypedHandlerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(TypedHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('routing', 'typed-handler') ?? [];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(TypedHandlerPayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        $typedExample = 'public function handle(TypedHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource';
        $untypedExample = 'public function handle(object $payload, ResourceInterface $resource): ResourceInterface';

        return $resource
            ->pageTitle('Typed Handler — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'typed-handler',
                'infoWhat' => $explanation['what'] ?? 'Concrete types in handle() — no instanceof, no casting, no guessing.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('typed-handler')
            ->withTitle('Typed Handler')
            ->withSummary('Concrete types in handle() — no instanceof, no casting, no guessing.')
            ->withEntryLine('Handlers declare concrete Payload and Resource types — the framework validates signatures at boot.')
            ->withHighlights(['TypedHandlerInterface', '#[AsPayloadHandler]', 'HandlerReflectionCache', 'concrete types'])
            ->withLearnMoreLabel('See the typed signature →')
            ->withDeepDiveLabel('How reflection validation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/signature-comparison.html.twig', [
                'typedSignature' => $typedExample,
                'untypedSignature' => $untypedExample,
            ]);
    }
}
