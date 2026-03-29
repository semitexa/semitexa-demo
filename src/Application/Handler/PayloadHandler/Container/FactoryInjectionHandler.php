<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\FactoryInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: FactoryInjectionPayload::class, resource: DemoFeatureResource::class)]
final class FactoryInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(FactoryInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('di', 'factory') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Factory Injection — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'di',
                'currentSlug' => 'factory',
                'infoWhat' => $explanation['what'] ?? 'Factory injections expose a validated selection point for fresh instances without reopening the container model.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('di')
            ->withSlug('factory')
            ->withTitle('Factory Injection')
            ->withSummary('On-demand creation stays explicit — lazy instances without falling back to service locator habits.')
            ->withEntryLine('On-demand creation stays explicit — lazy instances without falling back to service locator habits.')
            ->withHighlights(['#[InjectAsFactory]', 'closed-world selection', 'on-demand', 'lazy instantiation'])
            ->withLearnMoreLabel('See factory injection →')
            ->withDeepDiveLabel('Lazy instantiation patterns →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'On-Demand Creation',
                'title' => 'Shared factory, fresh product',
                'summary' => 'The factory is injected explicitly by the container. The object it returns is new on every call, but the creation path remains reviewable.',
                'codeSnippet' => "#[InjectAsFactory]\nprotected \\Closure \$mailerFactory;\n\npublic function handle(...): ... {\n    \$mailer = (\$this->mailerFactory)();\n    // New instance, still from an explicit DI path\n}",
                'note' => 'Use factories when creation must stay lazy. Do not fall back to ad-hoc container access inside services.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
