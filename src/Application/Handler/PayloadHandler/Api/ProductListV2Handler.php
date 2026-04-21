<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListV2Payload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProductListV2Payload::class, resource: DemoFeatureResource::class)]
final class ProductListV2Handler implements TypedHandlerInterface
{
    private const ENDPOINT_PATH = '/demo/api/active-version';
    private const API_VERSION = '2.0.0';

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ProductListV2Payload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $request = $payload->getHttpRequest() ?? new Request('GET', self::ENDPOINT_PATH, [], [], [], [], []);
        $body = $this->apiPresenter->buildCollection(request: $request, query: $payload->getQ());
        $contentType = $this->apiPresenter->getContentType($request, $payload->getFormat());

        // JSON clients bypass page rendering and receive the collection with the active-version header.
        if ($this->wantsJson($request, $payload->getFormat())) {
            return $this->jsonResponse($resource, $body, [
                'Content-Type' => $contentType,
                'X-Api-Version' => self::API_VERSION,
            ]);
        }

        $spec = new FeatureSpec(
            section: 'api',
            slug: 'active-version',
            entryLine: 'The active version should feel intentionally boring: same response shape, stable metadata, and no sunset chatter for clients to parse around.',
            learnMoreLabel: 'Inspect active response →',
            deepDiveLabel: 'Version contract internals →',
            relatedSlugs: [],
            fallbackTitle: 'Active Version',
            fallbackSummary: 'The current collection endpoint with a clean X-Api-Version header and no deprecation noise.',
            fallbackHighlights: ['#[ApiVersion]', 'X-Api-Version', 'active lifecycle'],
            explanation: $this->explanationProvider->getExplanation('api', 'active-version') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Active Version Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Active Version Payload' => $this->sourceCodeReader->readClassSource(ProductListV2Payload::class),
                'DemoApiPresenter' => $this->sourceCodeReader->readClassSource(DemoApiPresenter::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/api-endpoint-demo.html.twig', [
                'eyebrow' => 'Stable Contract',
                'title' => 'Current collection endpoint without lifecycle warnings',
                'summary' => 'This is the clean path Semitexa wants machine consumers to target when there is no migration pressure to communicate.',
                'method' => 'GET',
                'url' => self::ENDPOINT_PATH,
                'statusCode' => 200,
                'contentType' => 'application/json',
                'headers' => [
                    'Content-Type' => $contentType,
                    'X-Api-Version' => self::API_VERSION,
                ],
                'curlExample' => 'curl -i -H "Accept: application/json" "http://localhost:9502' . self::ENDPOINT_PATH . '"',
                'bodyLabel' => 'Active version payload',
                'body' => $this->encodeJson($body),
            ])
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
