<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsMutable;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\MutableInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: MutableInjectionPayload::class, resource: DemoFeatureResource::class)]
final class MutableInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(MutableInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('di', 'mutable') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Mutable Injection — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'di',
                'currentSlug' => 'mutable',
                'infoWhat' => $explanation['what'] ?? 'Mutable injections clone a service per request so handlers can keep transient state safely.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('di')
            ->withSlug('mutable')
            ->withTitle('Mutable Injection')
            ->withSummary('Request-scoped services get a fresh clone per request — safe state without global mutation.')
            ->withEntryLine('Request-scoped services get a fresh clone per request — safe state without global mutation.')
            ->withHighlights(['#[InjectAsMutable]', 'request-scoped', 'clone', 'state isolation'])
            ->withLearnMoreLabel('See mutable injection →')
            ->withDeepDiveLabel('Clone lifecycle under the hood →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Request Scope',
                'title' => 'Fresh clone for every request',
                'summary' => 'Mutable services can safely accumulate transient state because the container clones them per request.',
                'paragraphs' => [
                    'Any state collected during a request is discarded when the response completes.',
                ],
                'codeSnippet' => "#[InjectAsMutable]\nprotected RequestBag \$bag;\n\n// Each request gets a fresh clone:\n// \$this->bag !== <previous request\\'s bag>",
                'note' => 'Use mutable injection for session-like or request-specific context objects, not for shared worker services.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
