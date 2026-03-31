<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Api\GraphqlDerivedApiPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Graphql\Contract\GraphqlOperationRegistryInterface;
use Semitexa\Graphql\Discovery\ResolvedGraphqlOperation;

#[AsPayloadHandler(payload: GraphqlDerivedApiPayload::class, resource: DemoFeatureResource::class)]
final class GraphqlDerivedApiHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected GraphqlOperationRegistryInterface $operations;

    public function handle(GraphqlDerivedApiPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('api', 'rest-graphql') ?? [];
        $operations = array_map(
            fn (ResolvedGraphqlOperation $operation): array => $this->presentOperation($operation),
            $this->operations->all(),
        );

        return $resource
            ->pageTitle('REST + GraphQL — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'api',
                'currentSlug' => 'rest-graphql',
                'infoWhat' => $explanation['what'] ?? 'Semitexa can derive GraphQL-ready operations from the same Payload DTO and Handler contracts already used by REST.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('api')
            ->withSlug('rest-graphql')
            ->withTitle('REST + GraphQL')
            ->withSummary('One Semitexa use case can serve both REST and GraphQL without duplicating handler logic into separate resolver classes.')
            ->withEntryLine('Semitexa lets one use case answer both transports, so teams do not have to choose between REST and GraphQL too early.')
            ->withHighlights(['REST + GraphQL', '#[ExposeAsGraphql]', 'shared use case', 'no duplicated logic'])
            ->withLearnMoreLabel('See one use case with two transports →')
            ->withDeepDiveLabel('Why shared contracts matter here →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/graphql-derived-api.html.twig', [
                'operations' => $operations,
                'schemaPreview' => $this->buildSchemaPreview($operations),
            ])
            ->withSourceCode([
                'REST + GraphQL Payload' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Api/Graphql/ProductDetailPayload.example.php'),
                'REST + GraphQL Output' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Api/Graphql/ProductGraphqlView.example.php'),
                'POST /graphql Query' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Api/Graphql/ProductsQuery.example.graphql'),
            ])
            ->withExplanation($explanation)
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/graphql-derived-notes.html.twig', [
                'operations' => $operations,
            ]);
    }

    /**
     * @return array{
     *   field: string,
     *   rootType: string,
     *   outputClass: string,
     *   outputLabel: string,
     *   path: string,
     *   httpMethods: string,
     *   description: string,
     *   queryPreview: string
     * }
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
            $line = sprintf(
                '  %s: %s',
                $operation['field'],
                $this->shortClassName($operation['outputClass']),
            );

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
