<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Docs\Application\Document\DocumentId;
use Semitexa\Docs\Application\Service\DocumentHtmlRenderer;
use Semitexa\Docs\Application\Service\FileDocumentRepository;

#[AsService]
final class DemoDocumentAdapter
{
    #[InjectAsReadonly]
    protected FileDocumentRepository $repository;

    #[InjectAsReadonly]
    protected DocumentHtmlRenderer $renderer;

    public function loadFeatureDocument(string $section, string $slug, string $locale = 'en'): ?DemoFeatureDocument
    {
        $document = $this->repository->find(new DocumentId($section, $slug), $locale);
        if ($document === null) {
            return null;
        }

        return new DemoFeatureDocument(
            resolved: $document,
            rendered: $this->renderer->renderHtml($document),
        );
    }
}
