<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Api\GraphqlApiPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: GraphqlApiPayload::class, resource: DemoFeatureResource::class)]
final class GraphqlApiHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(GraphqlApiPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'api',
            'graphql',
            'GraphQL API',
            'GraphQL-first Semitexa contracts built with typed payloads and typed output DTOs instead of resolver sprawl.',
            ['POST /graphql', '#[ExposeAsGraphql]', 'typed output DTOs', 'GraphQL-first'],
        );
        $explanation = $this->explanationProvider->getExplanation('api', 'graphql') ?? [];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'api',
                'currentSlug' => 'graphql',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('api')
            ->withSlug('graphql')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('If your public API is GraphQL-first, Semitexa still keeps the application layer explicit and typed.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See a GraphQL-first payload →')
            ->withDeepDiveLabel('Why this is cleaner than resolver drift →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/graphql-api.html.twig', [
                'transport' => 'POST /graphql',
                'field' => 'productMetrics',
                'output' => 'ProductMetricsGraphqlView',
                'query' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductMetricsQuery.example.graphql'),
                'response' => "{\n  \"data\": {\n    \"productMetrics\": {\n      \"total\": 128,\n      \"active\": 117,\n      \"archived\": 11,\n      \"averagePrice\": 189.4\n    }\n  }\n}",
            ])
            ->withSourceCode([
                'GraphQL-Only Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductMetricsPayload.example.php'),
                'GraphQL-Only Output' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductMetricsGraphqlView.example.php'),
                'POST /graphql Query' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductMetricsQuery.example.graphql'),
            ])
            ->withExplanation($explanation)
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/graphql-api-notes.html.twig');
    }
}
