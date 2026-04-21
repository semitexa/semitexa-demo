<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Container\MutableInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: MutableInjectionPayload::class, resource: DemoFeatureResource::class)]
final class MutableInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(MutableInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'di',
            slug: 'mutable',
            entryLine: 'Execution-scoped services get a fresh clone every run — safe state without contaminating the worker.',
            learnMoreLabel: 'See mutable injection →',
            deepDiveLabel: 'Clone lifecycle under the hood →',
            relatedSlugs: [],
            fallbackTitle: 'Mutable Injection',
            fallbackSummary: 'Execution-scoped services get a fresh clone every run — safe state without contaminating the worker.',
            fallbackHighlights: ['#[InjectAsMutable]', 'execution-scoped', 'clone', 'state isolation'],
            explanation: $this->explanationProvider->getExplanation('di', 'mutable') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Execution Scope',
                'title' => 'Fresh clone for every execution',
                'summary' => 'Mutable services can safely accumulate transient state because the container clones them for each execution context.',
                'paragraphs' => [
                    'Any state collected during one execution is discarded when that HTTP request, console command, or async job completes.',
                ],
                'codeSnippet' => "#[InjectAsMutable]\nprotected ExecutionBag \$bag;\n\n// Each execution gets a fresh clone:\n// \$this->bag !== <previous execution\\'s bag>",
                'note' => 'Use mutable injection for execution-specific context objects, not for shared worker services.',
            ]);
    }
}
