<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\ParameterizedRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ParameterizedRoutePayload::class, resource: DemoFeatureResource::class)]
final class ParameterizedRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    private const PRODUCTS = [
        'headphones' => ['name' => 'Wireless Headphones', 'price' => '$79.99', 'category' => 'Audio'],
        'keyboard' => ['name' => 'Mechanical Keyboard', 'price' => '$129.99', 'category' => 'Input'],
        'monitor' => ['name' => 'Ultra-wide Monitor', 'price' => '$549.99', 'category' => 'Display'],
        'mouse' => ['name' => 'Ergonomic Mouse', 'price' => '$49.99', 'category' => 'Input'],
    ];

    public function handle(ParameterizedRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $slug = $payload->getSlug() ?: 'headphones';
        $product = self::PRODUCTS[$slug] ?? ['name' => 'Unknown Product', 'price' => 'N/A', 'category' => 'N/A'];
        $presentation = $this->documents->resolve(
            'routing',
            'parameterized',
            'Parameterized Route',
            'Path parameters with regex constraints and typed injection.',
            ['requirements', 'defaults', 'PayloadHydrator', 'setter injection'],
        );
        $explanation = $this->explanationProvider->getExplanation('routing', 'parameterized') ?? [];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(ParameterizedRoutePayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'parameterized',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('parameterized')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Path parameters like {slug} are extracted and injected via setters — with regex validation at the router level.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('Try different slugs →')
            ->withDeepDiveLabel('How regex compilation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/product-spotlight.html.twig', [
                'slug' => $slug,
                'product' => $product,
                'examples' => [
                    ['label' => 'keyboard', 'href' => '/demo/routing/parameterized/keyboard'],
                    ['label' => 'monitor', 'href' => '/demo/routing/parameterized/monitor'],
                    ['label' => 'mouse', 'href' => '/demo/routing/parameterized/mouse'],
                ],
            ]);
    }
}
