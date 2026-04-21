<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Api\GraphqlApiPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: GraphqlApiPayload::class, resource: DemoFeatureResource::class)]
final class GraphqlApiHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(GraphqlApiPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'api',
            slug: 'graphql',
            entryLine: 'If your public API is GraphQL-first, Semitexa still keeps the application layer explicit and typed.',
            learnMoreLabel: 'See a GraphQL-first payload →',
            deepDiveLabel: 'Why this is cleaner than resolver drift →',
            relatedSlugs: [],
            fallbackTitle: 'GraphQL API',
            fallbackSummary: 'GraphQL-first Semitexa contracts built with typed payloads and typed output DTOs instead of resolver sprawl.',
            fallbackHighlights: ['POST /graphql', '#[ExposeAsGraphql]', 'typed output DTOs', 'GraphQL-first'],
            explanation: $this->explanationProvider->getExplanation('api', 'graphql') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'GraphQL-Only Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductMetricsPayload.example.php'),
                'GraphQL-Only Output' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductMetricsGraphqlView.example.php'),
                'POST /graphql Query' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductMetricsQuery.example.graphql'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/graphql-api.html.twig', [
                'transport' => 'POST /graphql',
                'field' => 'productMetrics',
                'output' => 'ProductMetricsGraphqlView',
                'query' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductMetricsQuery.example.graphql'),
                'response' => "{\n  \"data\": {\n    \"productMetrics\": {\n      \"total\": 128,\n      \"active\": 117,\n      \"archived\": 11,\n      \"averagePrice\": 189.4\n    }\n  }\n}",
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/graphql-api-notes.html.twig', []);
    }
}
