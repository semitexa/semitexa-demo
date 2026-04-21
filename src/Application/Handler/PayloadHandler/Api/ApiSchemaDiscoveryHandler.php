<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Api\ApiSchemaDiscoveryPayload;
use Semitexa\Demo\Application\Payload\Request\Api\ProductDetailPayload;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListPayload;
use Semitexa\Demo\Application\Payload\Request\Api\ProductSchemaPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ApiSchemaDiscoveryPayload::class, resource: DemoFeatureResource::class)]
final class ApiSchemaDiscoveryHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    public function handle(ApiSchemaDiscoveryPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'api',
            slug: 'schema-discovery',
            entryLine: 'A machine-facing API should explain its own shape and let you exercise the contract without leaving the demo.',
            learnMoreLabel: 'Inspect the live contract →',
            deepDiveLabel: 'Schema generation notes →',
            relatedSlugs: [],
            fallbackTitle: 'Schema Discovery',
            fallbackSummary: 'A mini Swagger-style explorer for the live product API contract, schema endpoint, and response shapes.',
            fallbackHighlights: ['#[ExternalApi]', 'application/schema+json', 'JSON Schema', 'live explorer'],
            explanation: $this->explanationProvider->getExplanation('api', 'schema-discovery') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $operations = $this->buildOperations();

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Schema Discovery Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Schema Discovery Page Payload' => $this->sourceCodeReader->readClassSource(ApiSchemaDiscoveryPayload::class),
                'Schema Endpoint Payload' => $this->sourceCodeReader->readClassSource(ProductSchemaPayload::class),
                'Product List Payload' => $this->sourceCodeReader->readClassSource(ProductListPayload::class),
                'Product Detail Payload' => $this->sourceCodeReader->readClassSource(ProductDetailPayload::class),
                'DemoApiPresenter' => $this->sourceCodeReader->readClassSource(DemoApiPresenter::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/api-schema-explorer.html.twig', [
                'operations' => $operations,
                'initialOperation' => $operations[0] ?? null,
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/api-schema-notes.html.twig', [
                'operations' => $operations,
            ]);
    }

    /**
     * @return list<array{
     *   id: string,
     *   label: string,
     *   method: string,
     *   url: string,
     *   summary: string,
     *   headers: array<string, string>,
     *   responseStatus: int,
     *   responseContentType: string,
     *   responseBody: string
     * }>
     */
    private function buildOperations(): array
    {
        $schemaJson = $this->encodeJson($this->apiPresenter->buildProductSchema());
        $slimRequest = new Request('GET', '/demo/api/v1/products/wireless-headphones?fields=slug,name,price', [], [], [], [], []);
        $fullRequest = new Request('GET', '/demo/api/v1/products/wireless-headphones?profile=full&expand=category,reviews', ['X-Response-Profile' => 'full'], [], [], [], []);

        $slimBody = $this->apiPresenter->buildDetail(request: $slimRequest, slug: 'wireless-headphones', fields: 'slug,name,price');
        $fullBody = $this->apiPresenter->buildDetail(request: $fullRequest, slug: 'wireless-headphones', expand: 'category,reviews', profile: 'full');

        return [
            [
                'id' => 'schema-json',
                'label' => 'GET schema',
                'method' => 'GET',
                'url' => '/demo/api/v1/products/_schema',
                'summary' => 'Raw JSON schema contract for the product representation.',
                'headers' => ['Accept' => 'application/json'],
                'responseStatus' => 200,
                'responseContentType' => 'application/json',
                'responseBody' => $schemaJson,
            ],
            [
                'id' => 'schema-contract',
                'label' => 'GET schema+json',
                'method' => 'GET',
                'url' => '/demo/api/v1/products/_schema',
                'summary' => 'Same contract, explicit schema media type for machine tooling.',
                'headers' => ['Accept' => 'application/schema+json'],
                'responseStatus' => 200,
                'responseContentType' => 'application/schema+json',
                'responseBody' => $schemaJson,
            ],
            [
                'id' => 'detail-slim',
                'label' => 'GET slim detail',
                'method' => 'GET',
                'url' => '/demo/api/v1/products/wireless-headphones?fields=slug,name,price',
                'summary' => 'Sparse fieldset proving the contract can be trimmed intentionally.',
                'headers' => ['Accept' => 'application/json'],
                'responseStatus' => 200,
                'responseContentType' => 'application/json',
                'responseBody' => $this->encodeJson($slimBody),
            ],
            [
                'id' => 'detail-expanded',
                'label' => 'GET expanded detail',
                'method' => 'GET',
                'url' => '/demo/api/v1/products/wireless-headphones?profile=full&expand=category,reviews',
                'summary' => 'Expanded graph with review data for internal dashboards and rich clients.',
                'headers' => ['Accept' => 'application/json', 'X-Response-Profile' => 'full'],
                'responseStatus' => 200,
                'responseContentType' => 'application/json',
                'responseBody' => $this->encodeJson($fullBody),
            ],
        ];
    }

    private function encodeJson(array|null $payload): string
    {
        if ($payload === null) {
            return "{}\n";
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : "{}\n";
    }
}
