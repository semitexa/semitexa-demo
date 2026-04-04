<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
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
                'infoWhat' => $explanation['what'] ?? 'Mutable injections clone a service per execution so handlers can keep transient state safely.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('di')
            ->withSlug('mutable')
            ->withTitle('Mutable Injection')
            ->withSummary('Execution-scoped services get a fresh clone every run — safe state without contaminating the worker.')
            ->withEntryLine('Execution-scoped services get a fresh clone every run — safe state without contaminating the worker.')
            ->withHighlights(['#[InjectAsMutable]', 'execution-scoped', 'clone', 'state isolation'])
            ->withLearnMoreLabel('See mutable injection →')
            ->withDeepDiveLabel('Clone lifecycle under the hood →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Execution Scope',
                'title' => 'Fresh clone for every execution',
                'summary' => 'Mutable services can safely accumulate transient state because the container clones them for each execution context.',
                'paragraphs' => [
                    'Any state collected during one execution is discarded when that HTTP request, console command, or async job completes.',
                ],
                'codeSnippet' => "#[InjectAsMutable]\nprotected ExecutionBag \$bag;\n\n// Each execution gets a fresh clone:\n// \$this->bag !== <previous execution\\'s bag>",
                'note' => 'Use mutable injection for execution-specific context objects, not for shared worker services.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
