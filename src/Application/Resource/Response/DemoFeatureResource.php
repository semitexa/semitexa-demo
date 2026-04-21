<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Demo\Application\Feature\FeatureDescriptor;
use Semitexa\Ssr\Http\Response\HtmlResponse;

/**
 * Shared resource for all individual feature detail pages.
 *
 * Preferred entry point: {@see self::applyFeature()} — a single, pure call that
 * projects a {@see FeatureDescriptor} onto the resource state (data + SEO).
 *
 * The individual `withX()` setters remain for handlers not yet migrated. Those
 * marked `@deprecated` have legacy side effects (implicit SEO writes) that are
 * scheduled for removal once all feature handlers adopt `applyFeature()`.
 */
#[AsResource(
    handle: 'demo_feature',
    template: '@project-layouts-semitexa-demo/pages/feature.html.twig',
)]
class DemoFeatureResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    /**
     * Project a full feature description onto the resource in one pure call.
     *
     * Sets every field derived from the descriptor — including SEO metadata —
     * explicitly and deterministically. This is the canonical way to populate
     * a feature page resource; prefer it over the individual `withX()` setters.
     */
    public function applyFeature(FeatureDescriptor $feature): self
    {
        $presentation = $feature->presentation;

        $this->pageTitle($feature->pageTitle());
        $this->seoTag('description', $presentation->summary);
        $this->seoTagDefault('description', $feature->entryLine);
        $this->seoKeywords($feature->seoKeywords());

        $this
            ->with('section', $feature->section)
            ->with('slug', $feature->slug)
            ->with('featureTitle', $presentation->title)
            ->with('summary', $presentation->summary)
            ->with('entryLine', $feature->entryLine)
            ->with('highlights', $presentation->highlights)
            ->with('learnMoreLabel', $feature->learnMoreLabel)
            ->with('deepDiveLabel', $feature->deepDiveLabel);

        if ($presentation->documentBodyHtml !== null) {
            $this->with('documentBodyHtml', $presentation->documentBodyHtml);
        }

        if ($feature->sectionLabel !== null) {
            $this->with('sectionLabel', $feature->sectionLabel);
        }

        if ($feature->relatedPayloads !== []) {
            $this->with('relatedPayloads', $feature->relatedPayloads);
        }

        if ($presentation->resultPreviewTemplate !== null) {
            $this
                ->with('resultPreviewTemplate', $presentation->resultPreviewTemplate)
                ->with('resultPreviewData', $presentation->resultPreviewData);
        }

        if ($presentation->l2ContentTemplate !== null) {
            $this
                ->with('l2ContentTemplate', $presentation->l2ContentTemplate)
                ->with('l2ContentData', $presentation->l2ContentData);
        }

        if ($presentation->l3ContentTemplate !== null) {
            $this
                ->with('l3ContentTemplate', $presentation->l3ContentTemplate)
                ->with('l3ContentData', $presentation->l3ContentData);
        }

        return $this;
    }

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

    /**
     * @deprecated Use {@see self::applyFeature()}. Retires once all feature handlers migrate.
     *             This setter has a legacy side effect: it also writes the SEO `description` tag.
     */
    public function withSummary(string $summary): self
    {
        $this->seoTag('description', $summary);

        return $this->with('summary', $summary);
    }

    /**
     * @deprecated Use {@see self::applyFeature()}. Retires once all feature handlers migrate.
     *             This setter has a legacy side effect: it also writes the default SEO `description` tag.
     */
    public function withEntryLine(string $entryLine): self
    {
        $this->seoTagDefault('description', $entryLine);

        return $this->with('entryLine', $entryLine);
    }

    public function withHighlights(array $highlights): self
    {
        $this->seoKeywords($highlights);

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

    public function withExplanationData(array $explanation): self
    {
        return $this->with('explanation', $explanation);
    }

    public function withSourceCode(array $sourceCode): self
    {
        return $this->with('sourceCode', $sourceCode);
    }

    /**
     * @deprecated Use {@see self::applyFeature()}. Retires once all feature handlers migrate.
     *             This setter has legacy side effects: it also writes SEO `description` and keyword tags
     *             derived from the explanation payload.
     */
    public function withExplanation(array $explanation): self
    {
        if (isset($explanation['what']) && is_string($explanation['what']) && $explanation['what'] !== '') {
            $this->seoTagDefault('description', $explanation['what']);
        }

        if (isset($explanation['keywords']) && is_array($explanation['keywords']) && $explanation['keywords'] !== []) {
            /** @var array<int, string|array{term?: string, title?: string, label?: string, name?: string}> $keywords */
            $keywords = $explanation['keywords'];
            $this->seoKeywords(FeatureDescriptor::normalizeKeywords($keywords));
        }

        return $this->withExplanationData($explanation);
    }

    public function withRelatedPayloads(array $related): self
    {
        return $this->with('relatedPayloads', $related);
    }

    public function withDocumentBodyHtml(?string $html): self
    {
        return $this->with('documentBodyHtml', $html);
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
