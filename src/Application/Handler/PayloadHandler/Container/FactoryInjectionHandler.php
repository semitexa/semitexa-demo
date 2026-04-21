<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Container\FactoryInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: FactoryInjectionPayload::class, resource: DemoFeatureResource::class)]
final class FactoryInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(FactoryInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'di',
            slug: 'factory',
            entryLine: 'On-demand creation stays explicit — lazy instances without falling back to service locator habits.',
            learnMoreLabel: 'See factory injection →',
            deepDiveLabel: 'Lazy instantiation patterns →',
            relatedSlugs: [],
            fallbackTitle: 'Factory Injection',
            fallbackSummary: 'On-demand creation stays explicit — lazy instances without falling back to service locator habits.',
            fallbackHighlights: ['#[InjectAsFactory]', 'closed-world selection', 'on-demand', 'lazy instantiation'],
            explanation: $this->explanationProvider->getExplanation('di', 'factory') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'On-Demand Creation',
                'title' => 'Shared factory, fresh product',
                'summary' => 'The factory is injected explicitly by the container. The object it returns is new on every call, but the creation path remains reviewable.',
                'codeSnippet' => "#[InjectAsFactory]\nprotected \\Closure \$mailerFactory;\n\npublic function handle(...): ... {\n    \$mailer = (\$this->mailerFactory)();\n    // New instance, still from an explicit DI path\n}",
                'note' => 'Use factories when creation must stay lazy. Do not fall back to ad-hoc container access inside services.',
            ]);
    }
}
