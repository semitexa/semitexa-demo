<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Api\Pipeline\ExternalApiExceptionMapper;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Exception\AccessDeniedException;
use Semitexa\Core\Exception\ConflictException;
use Semitexa\Core\Exception\DomainException;
use Semitexa\Core\Exception\RateLimitException;
use Semitexa\Core\Exception\ValidationException;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Exception\DemoApiNotFoundException;
use Semitexa\Demo\Application\Payload\Request\Api\ApiErrorTriggerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ApiErrorTriggerPayload::class, resource: DemoFeatureResource::class)]
final class ApiErrorTriggerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(ApiErrorTriggerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'api',
            'structured-errors',
            'Structured Errors',
            'Throw domain exceptions and let semitexa-api map them into stable machine-readable error envelopes.',
            ['ExternalApiExceptionMapper', 'DomainException', 'error.context', 'request_id'],
        );
        $type = strtolower($payload->getType());
        $explanation = $this->explanationProvider->getExplanation('api', 'structured-errors') ?? [];
        $request = $payload->getHttpRequest() ?? new Request('GET', '/demo/api/errors/' . $type, [], [], [], [], []);
        $exception = $this->buildException($type);

        if ($this->wantsJson($request, $payload->getFormat())) {
            throw $exception;
        }

        [$title, $summary, $notes] = match ($type) {
            'validation' => [
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

        $headers = ['Content-Type' => 'application/json'];
        if ($exception instanceof RateLimitException) {
            $headers['Retry-After'] = (string) $exception->getRetryAfter();
        }

        $statusCode = $exception->getStatusCode()->value;
        $body = [
            'error' => [
                'code' => $exception->getErrorCode(),
                'message' => $exception->getMessage(),
                'context' => $exception->getErrorContext() !== [] ? $exception->getErrorContext() : new \stdClass(),
                'request_id' => null,
                'docs_url' => null,
            ],
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'api',
                'currentSlug' => 'structured-errors',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('api')
            ->withSlug('structured-errors')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('API failures should stay operationally useful. This page shows the exact error body a client would parse, not just the fact that the request failed.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
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

    private function buildException(string $type): DomainException
    {
        return match ($type) {
            'validation' => new ValidationException([
                'fields' => [
                    'The requested sparse fieldset is invalid.',
                    'Use only slug,name,price,description,status,category,rating,reviewCount.',
                ],
            ]),
            'forbidden' => new AccessDeniedException('The selected machine credential cannot access this resource.'),
            'conflict' => new ConflictException('The requested representation profile conflicts with the current endpoint state.'),
            'rate-limit' => new RateLimitException(45),
            default => new DemoApiNotFoundException('Demo API product', 'missing-product'),
        };
    }
}
