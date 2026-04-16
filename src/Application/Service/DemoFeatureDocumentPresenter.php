<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;

#[AsService]
final class DemoFeatureDocumentPresenter
{
    #[InjectAsReadonly]
    protected DemoDocumentAdapter $documents;

    #[InjectAsReadonly]
    protected DemoFeatureCompanionResolver $companions;

    /**
     * @param list<string> $fallbackHighlights
     */
    public function resolve(
        string $section,
        string $slug,
        string $fallbackTitle,
        string $fallbackSummary,
        array $fallbackHighlights = [],
    ): DemoFeaturePresentation {
        $document = $this->documents->loadFeatureDocument($section, $slug);
        $companion = $this->companions->resolve($section, $slug, $document);

        return new DemoFeaturePresentation(
            title: $document?->resolved->metadata->title ?? $fallbackTitle,
            summary: $document?->resolved->metadata->summary ?? $fallbackSummary,
            highlights: $document?->resolved->metadata->keywords ?? $fallbackHighlights,
            documentBodyHtml: $document?->rendered->content,
            resultPreviewTemplate: $companion->resultPreviewTemplate,
            resultPreviewData: $companion->resultPreviewData,
            l2ContentTemplate: $companion->l2ContentTemplate,
            l2ContentData: $companion->l2ContentData,
            l3ContentTemplate: $companion->l3ContentTemplate,
            l3ContentData: $companion->l3ContentData,
        );
    }
}
