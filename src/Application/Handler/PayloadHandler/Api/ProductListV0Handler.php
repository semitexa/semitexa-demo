<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListV0Payload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProductListV0Payload::class, resource: DemoFeatureResource::class)]
final class ProductListV0Handler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ProductListV0Payload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $request = $payload->getHttpRequest() ?? new Request('GET', '/demo/api/v0/products', [], [], [], [], []);
        $body = $this->apiPresenter->buildCollection(request: $request, query: $payload->getQ());
        $explanation = $this->explanationProvider->getExplanation('api', 'sunset-version') ?? [];
        $contentType = $this->apiPresenter->getContentType($request, $payload->getFormat());

        if ($this->wantsJson($request, $payload->getFormat())) {
            return $this->jsonResponse($resource, $body, [
                'Content-Type' => $contentType,
                'X-Api-Version' => '0.9.0',
                'Deprecation' => '2025-06-01',
                'Sunset' => '2026-06-01',
            ]);
        }

        return $resource
            ->pageTitle('Sunset Version — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'api',
                'currentSlug' => 'sunset-version',
                'infoWhat' => $explanation['what'] ?? null,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('api')
            ->withSlug('sunset-version')
            ->withTitle('Sunset Version')
            ->withSummary('A deprecated product endpoint that emits both Deprecation and Sunset headers.')
            ->withEntryLine('Deprecated API versions should still be understandable: the response body is intact, but the headers clearly say the contract is on the way out.')
            ->withHighlights(['#[ApiVersion]', 'Deprecation', 'Sunset', 'X-Api-Version'])
            ->withLearnMoreLabel('Inspect sunset response →')
            ->withDeepDiveLabel('Version lifecycle internals →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/api-endpoint-demo.html.twig', [
                'eyebrow' => 'Version Lifecycle',
                'title' => 'Deprecated collection endpoint with explicit retirement headers',
                'summary' => 'The page keeps the actual JSON payload visible, but the important story is in the lifecycle metadata wrapped around it.',
                'method' => 'GET',
                'url' => '/demo/api/v0/products',
                'statusCode' => 200,
                'contentType' => 'application/json',
                'headers' => [
                    'Content-Type' => $contentType,
                    'X-Api-Version' => '0.9.0',
                    'Deprecation' => '2025-06-01',
                    'Sunset' => '2026-06-01',
                ],
                'curlExample' => 'curl -i -H "Accept: application/json" http://localhost:9502/demo/api/v0/products',
                'bodyLabel' => 'Deprecated version payload',
                'body' => $this->encodeJson($body),
            ])
            ->withSourceCode([
                'Sunset Version Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Sunset Version Payload' => $this->sourceCodeReader->readClassSource(ProductListV0Payload::class),
                'DemoApiPresenter' => $this->sourceCodeReader->readClassSource(DemoApiPresenter::class),
            ])
            ->withExplanation($explanation)
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/api-endpoint-notes.html.twig', [
                'eyebrow' => 'Lifecycle Notes',
                'title' => 'What this endpoint is proving',
                'notes' => [
                    [
                        'concern' => 'Client warning',
                        'behavior' => 'The body still resolves successfully, but `Deprecation` and `Sunset` tell consumers to leave the route.',
                        'why' => 'Retirement notice should be explicit without immediately breaking integrations.',
                    ],
                    [
                        'concern' => 'Version traceability',
                        'behavior' => '`X-Api-Version: 0.9.0` makes the serving contract visible in every response.',
                        'why' => 'Observability and support workflows need a precise contract version, not guesses from the URL alone.',
                    ],
                ],
            ]);
    }

    /**
     * @param array<mixed> $payload
     */
    private function encodeJson(array $payload): string
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : "{}\n";
    }

    private function wantsJson(Request $request, ?string $format): bool
    {
        return strtolower((string) $format) === 'json'
            || str_contains(strtolower($request->getHeader('Accept') ?? ''), 'application/json');
    }

    /**
     * @param array<mixed> $payload
     * @param array<string, string> $headers
     */
    private function jsonResponse(DemoFeatureResource $resource, array $payload, array $headers): DemoFeatureResource
    {
        $resource->disableAutoRender();
        $resource->setContent($this->encodeJson($payload));

        foreach ($headers as $name => $value) {
            $resource->setHeader($name, $value);
        }

        return $resource;
    }
}
