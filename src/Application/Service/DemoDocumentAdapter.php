<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Docs\Domain\Model\DocumentId;
use Semitexa\Docs\Application\Service\DocumentHtmlRenderer;
use Semitexa\Docs\Application\Service\FileDocumentRepository;

#[AsService]
final class DemoDocumentAdapter
{
    /**
     * The demo runs on framework.semitexa.com, which serves no /docs: a link
     * from a feature page to another document goes to the docs site, where
     * every page exists, not to a /demo page only some documents have.
     */
    private const DOCS_SITE = 'https://semitexa.com/docs/';

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
            rendered: $this->renderer->renderHtml($document, self::DOCS_SITE),
        );
    }
}
