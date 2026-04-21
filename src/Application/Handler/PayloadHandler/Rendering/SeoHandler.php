<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Rendering\SeoPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SeoPayload::class, resource: DemoFeatureResource::class)]
final class SeoHandler implements TypedHandlerInterface
{
    private const ENTRY_LINE = 'Set title, description, and Open Graph tags from your handler without touching Twig templates.';

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(SeoPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'seo',
            entryLine: self::ENTRY_LINE,
            learnMoreLabel: 'See SEO methods →',
            deepDiveLabel: 'SEO pipeline internals →',
            relatedSlugs: [],
            fallbackTitle: 'SEO',
            fallbackSummary: 'Set title, description, and Open Graph tags from your handler — no template hacks needed.',
            fallbackHighlights: ['pageTitle()', 'seoTag()', 'Open Graph', 'description', 'structured data'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'seo') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $pageTitle = $this->projector->describe($spec)->pageTitle();

        return $this->projector->project($resource, $spec)
            ->seoTag('og:title', $pageTitle)
            ->seoTag('og:description', self::ENTRY_LINE)
            ->seoTag('og:type', 'website')
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
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
            ]);
    }
}
