<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\AiConsolePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;

#[AsPayloadHandler(payload: AiConsolePayload::class, resource: DemoFeatureResource::class)]
final class AiConsoleHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(AiConsolePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'get-started',
            'ai-console',
            'AI Console',
            'Use `bin/semitexa ai` as an alternative CLI entrypoint when you do not want to remember exact command names.',
            ['bin/semitexa ai', 'natural-language prompts', 'experimental', 'command translation'],
        );

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'ai-console',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('ai-console')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('For common maintenance and discovery work you can ask the CLI in plain language instead of recalling every exact command from memory.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the AI console flow →')
            ->withDeepDiveLabel('When to use it and when not to →')
            ->withResultPreviewTemplate($presentation->resultPreviewTemplate ?? '', $presentation->resultPreviewData)
            ->withL2ContentTemplate($presentation->l2ContentTemplate ?? '', $presentation->l2ContentData);
    }
}
