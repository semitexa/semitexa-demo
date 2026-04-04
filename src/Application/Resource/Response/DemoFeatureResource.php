<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Http\Response\HtmlResponse;

/**
 * Shared resource for all individual feature detail pages.
 *
 * Handlers call renderTemplate() explicitly with feature-specific templates,
 * or use the default feature.html.twig three-layer template.
 */
#[AsResource(
    handle: 'demo_feature',
    template: '@project-layouts-semitexa-demo/pages/feature.html.twig',
)]
class DemoFeatureResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    public function withSection(string $section): self
    {
        return $this->with('section', $section);
    }

    public function withSlug(string $slug): self
    {
        return $this->with('slug', $slug);
    }

    public function withSectionLabel(string $label): self
    {
        return $this->with('sectionLabel', $label);
    }

    public function withTitle(string $title): self
    {
        return $this->with('featureTitle', $title);
    }

    public function withSummary(string $summary): self
    {
        return $this->with('summary', $summary);
    }

    public function withEntryLine(string $entryLine): self
    {
        return $this->with('entryLine', $entryLine);
    }

    public function withHighlights(array $highlights): self
    {
        return $this->with('highlights', $highlights);
    }

    public function withLearnMoreLabel(string $label): self
    {
        return $this->with('learnMoreLabel', $label);
    }

    public function withDeepDiveLabel(string $label): self
    {
        return $this->with('deepDiveLabel', $label);
    }

    public function withSourceCode(array $sourceCode): self
    {
        return $this->with('sourceCode', $sourceCode);
    }

    public function withExplanation(array $explanation): self
    {
        return $this->with('explanation', $explanation);
    }

    public function withRelatedPayloads(array $related): self
    {
        return $this->with('relatedPayloads', $related);
    }

    public function withResultPreview(string $preview): self
    {
        return $this->with('resultPreview', $preview);
    }

    public function withResultPreviewTemplate(string $template, array $data = []): self
    {
        return $this
            ->with('resultPreviewTemplate', $template)
            ->with('resultPreviewData', $data);
    }

    public function withL2Content(string $content): self
    {
        return $this->with('l2Content', $content);
    }

    public function withL2ContentTemplate(string $template, array $data = []): self
    {
        return $this
            ->with('l2ContentTemplate', $template)
            ->with('l2ContentData', $data);
    }

    public function withL3Content(string $content): self
    {
        return $this->with('l3Content', $content);
    }

    public function withL3ContentTemplate(string $template, array $data = []): self
    {
        return $this
            ->with('l3ContentTemplate', $template)
            ->with('l3ContentData', $data);
    }
}
