<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsMutable;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\MutableInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: MutableInjectionPayload::class, resource: DemoFeatureResource::class)]
final class MutableInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(MutableInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>Mutable services are <strong>cloned per request</strong>. '
            . 'Any state they accumulate during the request lifecycle is discarded when the response is sent.</p>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "#[InjectAsMutable]\n"
                . "protected RequestBag \$bag;\n\n"
                . "// Each request gets a fresh clone:\n"
                . "// \$this->bag !== <previous request's bag>"
            )
            . '</pre>'
            . '<p class="note">Use <code>#[InjectAsMutable]</code> for services that hold request-specific state '
            . '(e.g. session, request context, per-request counters).</p>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('di', 'mutable') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Mutable Injection — Semitexa Demo')
            ->withSection('di')
            ->withSlug('mutable')
            ->withTitle('Mutable Injection')
            ->withSummary('Request-scoped services get a fresh clone per request — safe state without global mutation.')
            ->withEntryLine('Request-scoped services get a fresh clone per request — safe state without global mutation.')
            ->withHighlights(['#[InjectAsMutable]', 'request-scoped', 'clone', 'state isolation'])
            ->withLearnMoreLabel('See mutable injection →')
            ->withDeepDiveLabel('Clone lifecycle under the hood →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
