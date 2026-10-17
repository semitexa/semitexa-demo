<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;

/**
 * The catalog the ⌘K documentation search filters: every section and feature
 * page with the words it should be found by.
 *
 * It used to be rendered into every page as a hidden list. MEASURED on
 * framework.semitexa.com 2026-10-16: 72 KB of a 157 KB page and 2,044 of its
 * 2,712 words were that list, identical on every demo page, so crawlers read
 * each page as mostly the same text. Now the dialog fetches it from
 * {@see \Semitexa\Demo\Application\Payload\Request\Search\DocsSearchIndexPayload}
 * the first time it opens.
 */
#[AsService]
final class DocsSearchIndex
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    /**
     * @return list<array{title: string, section: string, summary: string, href: string, searchText: string}>
     */
    public function items(): array
    {
        $index = [];

        foreach ($this->catalog->getSections() as $section) {
            $sectionKey = trim((string) ($section['key'] ?? ''));
            $sectionLabel = trim((string) ($section['label'] ?? $sectionKey));
            $sectionHref = trim((string) ($section['href'] ?? ''));

            if ($sectionLabel !== '' && $sectionHref !== '') {
                $index[] = self::item(
                    title: $sectionLabel,
                    section: 'Section',
                    summary: trim((string) ($section['summary'] ?? '')),
                    href: $sectionHref,
                    terms: [$sectionKey],
                );
            }

            foreach (($section['features'] ?? []) as $feature) {
                if (!is_array($feature)) {
                    continue;
                }

                $title = trim((string) ($feature['title'] ?? ''));
                $href = trim((string) ($feature['href'] ?? ''));
                if ($title === '' || $href === '') {
                    continue;
                }

                $terms = [$sectionKey, (string) ($feature['slug'] ?? '')];
                foreach (['aliases', 'keywords'] as $termGroup) {
                    foreach (($feature[$termGroup] ?? []) as $term) {
                        if (is_string($term) && trim($term) !== '') {
                            $terms[] = $term;
                        }
                    }
                }

                $index[] = self::item(
                    title: $title,
                    section: $sectionLabel,
                    summary: trim((string) ($feature['summary'] ?? '')),
                    href: $href,
                    terms: $terms,
                );
            }
        }

        return $index;
    }

    /**
     * @param list<string> $terms
     * @return array{title: string, section: string, summary: string, href: string, searchText: string}
     */
    private static function item(string $title, string $section, string $summary, string $href, array $terms): array
    {
        return [
            'title' => $title,
            'section' => $section,
            'summary' => $summary,
            'href' => $href,
            'searchText' => mb_strtolower(implode(' ', [$title, $section, $summary, ...$terms])),
        ];
    }
}
