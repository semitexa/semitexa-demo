<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Api\GraphqlDerivedApiPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Graphql\Contract\GraphqlOperationRegistryInterface;
use Semitexa\Graphql\Discovery\ResolvedGraphqlOperation;

#[AsPayloadHandler(payload: GraphqlDerivedApiPayload::class, resource: DemoFeatureResource::class)]
final class GraphqlDerivedApiHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected GraphqlOperationRegistryInterface $operations;

    public function handle(GraphqlDerivedApiPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'api',
            slug: 'rest-graphql',
            entryLine: 'Semitexa lets one use case answer both transports, so teams do not have to choose between REST and GraphQL too early.',
            learnMoreLabel: 'See one use case with two transports →',
            deepDiveLabel: 'Why shared contracts matter here →',
            relatedSlugs: [],
            fallbackTitle: 'REST + GraphQL',
            fallbackSummary: 'One Semitexa use case can serve both REST and GraphQL without duplicating handler logic into separate resolver classes.',
            fallbackHighlights: ['REST + GraphQL', '#[ExposeAsGraphql]', 'shared use case', 'no duplicated logic'],
            explanation: $this->explanationProvider->getExplanation('api', 'rest-graphql') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $operations = array_map(
            fn (ResolvedGraphqlOperation $operation): array => $this->presentOperation($operation),
            $this->operations->all(),
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'REST + GraphQL Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductDetailPayload.example.php'),
                'REST + GraphQL Output' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductGraphqlView.example.php'),
                'POST /graphql Query' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Graphql/ProductsQuery.example.graphql'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/graphql-derived-api.html.twig', [
                'operations' => $operations,
                'schemaPreview' => $this->buildSchemaPreview($operations),
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/graphql-derived-notes.html.twig', [
                'operations' => $operations,
            ]);
    }

    /**
     * @return array{field: string, rootType: string, outputClass: string, outputLabel: string, path: string, httpMethods: string, description: string, queryPreview: string}
     */
    private function presentOperation(ResolvedGraphqlOperation $operation): array
    {
        $outputClass = $operation->outputClass ?? 'Unspecified output contract';

        return [
            'field' => $operation->field,
            'rootType' => $operation->rootType,
            'outputClass' => $outputClass,
            'outputLabel' => $this->shortClassName($outputClass),
            'path' => $operation->path,
            'httpMethods' => implode(', ', $operation->httpMethods),
            'description' => $operation->description,
            'queryPreview' => $this->buildQueryPreview($operation),
        ];
    }

    /**
     * @param list<array{field: string, rootType: string, outputClass: string, outputLabel: string, description: string, queryPreview: string}> $operations
     */
    private function buildSchemaPreview(array $operations): string
    {
        $queries = [];
        $mutations = [];

        foreach ($operations as $operation) {
            $line = sprintf('  %s: %s', $operation['field'], $this->shortClassName($operation['outputClass']));
            if ($operation['rootType'] === 'mutation') {
                $mutations[] = $line;
                continue;
            }
            $queries[] = $line;
        }

        $blocks = [];
        if ($queries !== []) {
            $blocks[] = "type Query {\n" . implode("\n", $queries) . "\n}";
        }
        if ($mutations !== []) {
            $blocks[] = "type Mutation {\n" . implode("\n", $mutations) . "\n}";
        }

        return implode("\n\n", $blocks);
    }

    private function buildQueryPreview(ResolvedGraphqlOperation $operation): string
    {
        if ($operation->field === 'productBySlug') {
            return <<<GRAPHQL
query {
  productBySlug {
    slug
    name
    price
    category {
      name
    }
  }
}
GRAPHQL;
        }

        return <<<GRAPHQL
query {
  products {
    items {
      slug
      name
      price
    }
    total
    page
    limit
  }
}
GRAPHQL;
    }

    private function shortClassName(string $className): string
    {
        if (!str_contains($className, '\\')) {
            return $className;
        }

        $parts = explode('\\', $className);

        return end($parts) ?: $className;
    }
}
