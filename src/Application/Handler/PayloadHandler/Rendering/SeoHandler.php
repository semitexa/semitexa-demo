<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\SeoPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SeoPayload::class, resource: DemoFeatureResource::class)]
final class SeoHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(SeoPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('rendering', 'seo') ?? [];
        $description = 'Set title, description, and Open Graph tags from your handler without touching Twig templates.';
        $title = 'SEO — Semitexa Demo';

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($title)
            ->seoTag('description', $description)
            ->seoTag('og:title', $title)
            ->seoTag('og:description', $description)
            ->seoTag('og:type', 'website')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'seo',
                'infoWhat' => $explanation['what'] ?? 'Handlers can set title, Open Graph, and canonical metadata directly without template overrides.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('seo')
            ->withTitle('SEO')
            ->withSummary($description)
            ->withEntryLine($description)
            ->withHighlights(['pageTitle()', 'seoTag()', 'Open Graph', 'description', 'structured data'])
            ->withLearnMoreLabel('See SEO methods →')
            ->withDeepDiveLabel('SEO pipeline internals →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Metadata Control',
                'title' => 'SEO tags come from the handler',
                'summary' => 'This page sets its own title and social metadata directly in the response resource, not via template overrides.',
                'columns' => ['Tag', 'Source', 'Value on this page'],
                'rows' => [
                    [['text' => '<title>', 'code' => true], ['text' => 'pageTitle()', 'code' => true], ['text' => 'SEO — Semitexa Demo']],
                    [['text' => 'description', 'code' => true], ['text' => 'seoTag()', 'code' => true], ['text' => 'Set title, description…']],
                    [['text' => 'og:title', 'code' => true], ['text' => 'seoTag()', 'code' => true], ['text' => 'SEO — Semitexa Demo']],
                    [['text' => 'og:description', 'code' => true], ['text' => 'seoTag()', 'code' => true], ['text' => 'Set title, description…']],
                    [['text' => 'og:type', 'code' => true], ['text' => 'seoTag()', 'code' => true], ['text' => 'website']],
                ],
                'codeSnippet' => "return \$resource\n    ->pageTitle('SEO — Semitexa Demo')\n    ->seoTag('description', 'Set title, description, and Open Graph tags…')\n    ->seoTag('og:title', 'SEO — Semitexa Demo');",
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
