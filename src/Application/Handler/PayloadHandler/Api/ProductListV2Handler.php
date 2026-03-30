<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListV2Payload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProductListV2Payload::class, resource: DemoFeatureResource::class)]
final class ProductListV2Handler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ProductListV2Payload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $request = $payload->getHttpRequest() ?? new Request('GET', '/demo/api/v2/products', [], [], [], [], []);
        $body = $this->apiPresenter->buildCollection(request: $request, query: $payload->getQ());
        $explanation = $this->explanationProvider->getExplanation('api', 'active-version') ?? [];

        return $resource
            ->pageTitle('Active Version — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'api',
                'currentSlug' => 'active-version',
                'infoWhat' => $explanation['what'] ?? null,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('api')
            ->withSlug('active-version')
            ->withTitle('Active Version')
            ->withSummary('The current collection endpoint with a clean X-Api-Version header and no deprecation noise.')
            ->withEntryLine('The active version should feel intentionally boring: same response shape, stable metadata, and no sunset chatter for clients to parse around.')
            ->withHighlights(['#[ApiVersion]', 'X-Api-Version', 'active lifecycle'])
            ->withLearnMoreLabel('Inspect active response →')
            ->withDeepDiveLabel('Version contract internals →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/api-endpoint-demo.html.twig', [
                'eyebrow' => 'Stable Contract',
                'title' => 'Current collection endpoint without lifecycle warnings',
                'summary' => 'This is the clean path Semitexa wants machine consumers to target when there is no migration pressure to communicate.',
                'method' => 'GET',
                'url' => '/demo/api/v2/products',
                'statusCode' => 200,
                'contentType' => 'application/json',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Api-Version' => '2.0.0',
                ],
                'curlExample' => 'curl -H "Accept: application/json" http://localhost:9502/demo/api/v2/products',
                'bodyLabel' => 'Active version payload',
                'body' => $this->encodeJson($body),
            ])
            ->withSourceCode([
                'Active Version Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Active Version Payload' => $this->sourceCodeReader->readClassSource(ProductListV2Payload::class),
                'DemoApiPresenter' => $this->sourceCodeReader->readClassSource(DemoApiPresenter::class),
            ])
            ->withExplanation($explanation)
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/api-endpoint-notes.html.twig', [
                'eyebrow' => 'Version Notes',
                'title' => 'What this endpoint is proving',
                'notes' => [
                    [
                        'concern' => 'Current version',
                        'behavior' => 'The route emits `X-Api-Version: 2.0.0` and nothing else.',
                        'why' => 'Clients get stable version traceability without deprecation churn.',
                    ],
                    [
                        'concern' => 'Consumer expectation',
                        'behavior' => 'The JSON contract is the same style as older versions, but with no retirement metadata.',
                        'why' => 'Migration pressure should disappear once a client is on the supported path.',
                    ],
                ],
            ]);
    }

    private function encodeJson(array $payload): string
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : "{}\n";
    }
}
