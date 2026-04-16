<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

final readonly class DemoFeaturePresentation
{
    /**
     * @param list<string> $highlights
     * @param array<string, mixed> $resultPreviewData
     * @param array<string, mixed> $l2ContentData
     * @param array<string, mixed> $l3ContentData
     */
    public function __construct(
        public string $title,
        public string $summary,
        public array $highlights,
        public ?string $documentBodyHtml,
        public ?string $resultPreviewTemplate = null,
        public array $resultPreviewData = [],
        public ?string $l2ContentTemplate = null,
        public array $l2ContentData = [],
        public ?string $l3ContentTemplate = null,
        public array $l3ContentData = [],
    ) {}
}
