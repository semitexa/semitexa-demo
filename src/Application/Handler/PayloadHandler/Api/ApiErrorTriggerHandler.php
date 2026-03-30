<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Exception\DemoApiNotFoundException;
use Semitexa\Demo\Application\Payload\Request\Api\ApiErrorTriggerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Api\Pipeline\ExternalApiExceptionMapper;

#[AsPayloadHandler(payload: ApiErrorTriggerPayload::class, resource: DemoFeatureResource::class)]
final class ApiErrorTriggerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ApiErrorTriggerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $type = strtolower($payload->getType());
        $explanation = $this->explanationProvider->getExplanation('api', 'structured-errors') ?? [];

        [$statusCode, $headers, $body, $title, $summary, $notes] = match ($type) {
            'validation' => [
                422,
                ['Content-Type' => 'application/json'],
                [
                    'error' => [
                        'code' => 'validation_failed',
                        'message' => 'Validation failed.',
                        'context' => [
                            'fields' => [
                                'The requested sparse fieldset is invalid.',
                                'Use only slug,name,price,description,status,category,rating,reviewCount.',
                            ],
                        ],
                        'request_id' => null,
                        'docs_url' => null,
                    ],
                ],
                'Validation envelope',
                'Field-level contract failures stay machine-readable instead of collapsing into one opaque message.',
                [
                    [
                        'concern' => 'Field errors',
                        'behavior' => 'Validation problems stay nested under `error.context.fields`.',
                        'why' => 'Clients can bind UI or retry logic to structured field feedback.',
                    ],
                ],
            ],
            'forbidden' => [
                403,
                ['Content-Type' => 'application/json'],
                [
                    'error' => [
                        'code' => 'access_denied',
                        'message' => 'The selected machine credential cannot access this resource.',
                        'context' => new \stdClass(),
                        'request_id' => null,
                        'docs_url' => null,
                    ],
                ],
                'Forbidden envelope',
                'Authorization failures keep the same outer shape, so clients do not need a second parsing path.',
                [
                    [
                        'concern' => 'Auth semantics',
                        'behavior' => 'The HTTP status changes, but the JSON envelope stays identical in shape.',
                        'why' => 'SDKs and dashboards can branch on `error.code` with one parser.',
                    ],
                ],
            ],
            'conflict' => [
                409,
                ['Content-Type' => 'application/json'],
                [
                    'error' => [
                        'code' => 'conflict',
                        'message' => 'The requested representation profile conflicts with the current endpoint state.',
                        'context' => new \stdClass(),
                        'request_id' => null,
                        'docs_url' => null,
                    ],
                ],
                'Conflict envelope',
                'State conflicts travel through the same error contract without leaking framework internals.',
                [
                    [
                        'concern' => 'State mismatch',
                        'behavior' => 'Conflict errors remain explicit and parsable at the contract level.',
                        'why' => 'Client retry or fallback decisions should not require message scraping.',
                    ],
                ],
            ],
            'rate-limit' => [
                429,
                ['Content-Type' => 'application/json', 'Retry-After' => '45'],
                [
                    'error' => [
                        'code' => 'rate_limited',
                        'message' => 'Too many requests.',
                        'context' => ['retry_after' => 45],
                        'request_id' => null,
                        'docs_url' => null,
                    ],
                ],
                'Rate-limit envelope',
                'Retry semantics stay visible in both the header and the machine-readable body context.',
                [
                    [
                        'concern' => 'Retry guidance',
                        'behavior' => '`Retry-After` and `error.context.retry_after` both carry the wait time.',
                        'why' => 'Infrastructure and application clients can consume the same signal at different layers.',
                    ],
                ],
            ],
            default => [
                404,
                ['Content-Type' => 'application/json'],
                [
                    'error' => [
                        'code' => 'not_found',
                        'message' => 'Demo API product #missing-product not found.',
                        'context' => [
                            'resource' => 'Demo API product',
                            'identifier' => 'missing-product',
                        ],
                        'request_id' => null,
                        'docs_url' => null,
                    ],
                ],
                'Not-found envelope',
                'The route throws a domain exception, but the external API pipeline still returns one predictable error envelope.',
                [
                    [
                        'concern' => 'Exception mapping',
                        'behavior' => 'A typed domain exception becomes `{ error: { code, message, context, request_id } }`.',
                        'why' => 'Clients can rely on error structure even when the business path fails.',
                    ],
                    [
                        'concern' => 'Context preservation',
                        'behavior' => 'Resource name and identifier stay in `error.context` instead of being buried in text.',
                        'why' => 'Support tooling can diagnose failures without brittle string parsing.',
                    ],
                ],
            ],
        };

        return $resource
            ->pageTitle('Structured Errors — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'api',
                'currentSlug' => 'structured-errors',
                'infoWhat' => $explanation['what'] ?? null,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('api')
            ->withSlug('structured-errors')
            ->withTitle('Structured Errors')
            ->withSummary('Throw domain exceptions and let semitexa-api map them into stable machine-readable error envelopes.')
            ->withEntryLine('API failures should stay operationally useful. This page shows the exact error body a client would parse, not just the fact that the request failed.')
            ->withHighlights(['ExternalApiExceptionMapper', 'DomainException', 'error.context', 'request_id'])
            ->withLearnMoreLabel('Inspect error envelope →')
            ->withDeepDiveLabel('Error mapping internals →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/api-endpoint-demo.html.twig', [
                'eyebrow' => 'Failure Contract',
                'title' => $title,
                'summary' => $summary,
                'method' => 'GET',
                'url' => '/demo/api/errors/' . $type,
                'statusCode' => $statusCode,
                'contentType' => 'application/json',
                'headers' => $headers,
                'curlExample' => 'curl -H "Accept: application/json" http://localhost:9502/demo/api/errors/' . $type,
                'bodyLabel' => 'Machine-readable error body',
                'body' => $this->encodeJson($body),
            ])
            ->withSourceCode([
                'Structured Errors Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Structured Errors Payload' => $this->sourceCodeReader->readClassSource(ApiErrorTriggerPayload::class),
                'ExternalApiExceptionMapper' => $this->sourceCodeReader->readClassSource(ExternalApiExceptionMapper::class),
                'DemoApiNotFoundException' => $this->sourceCodeReader->readClassSource(DemoApiNotFoundException::class),
            ])
            ->withExplanation($explanation)
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/api-endpoint-notes.html.twig', [
                'eyebrow' => 'Envelope Notes',
                'title' => 'What this endpoint is proving',
                'notes' => $notes,
            ]);
    }

    private function encodeJson(array $payload): string
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : "{}\n";
    }
}
