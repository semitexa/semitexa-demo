<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Feature;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;

/**
 * Projects a handler-authored {@see FeatureSpec} onto a {@see DemoFeatureResource}.
 *
 * Owns the three-step pipeline that every feature page handler would otherwise
 * repeat:
 *
 *   1. Resolve the docs-backed presentation (title, summary, highlights, body).
 *   2. Enrich the spec with catalog-backed navigation data (related-link titles + hrefs,
 *      plus the section label when the handler leaves it implicit).
 *   3. Apply the resulting {@see FeatureDescriptor} to the resource and attach the
 *      demo shell context for the page.
 *
 * Handlers depend on this projector alone and stay focused on the page's
 * identity and copy — they do not touch the catalog, the docs manifest, or
 * the shell-context shape directly.
 */
#[AsService]
final class DemoFeaturePageProjector
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    /**
     * Apply a spec to a feature resource. The return type is generic over the concrete
     * resource class so tenant-showcase and other subclasses keep their own `with*()`
     * surface usable for subsequent chaining without casts.
     *
     * @template T of DemoFeatureResource
     * @param T $resource
     * @return T
     */
    public function project(DemoFeatureResource $resource, FeatureSpec $spec): DemoFeatureResource
    {
        $explanation = $this->normalizeExplanation($spec->explanation);
        $descriptor = $this->describe($spec);

        $resource
            ->applyFeature($descriptor)
            ->withDemoShellContext($this->shellContextFor($descriptor, $explanation));

        if ($explanation !== null) {
            $resource->withExplanationData($explanation);
        }

        return $resource;
    }

    /**
     * Resolve a {@see FeatureSpec} into the fully enriched {@see FeatureDescriptor}.
     *
     * Most handlers do not need to call this directly — {@see self::project()} runs describe internally.
     * Exposed for the rare handler that needs the resolved page title or highlights to synthesize
     * additional SEO metadata (e.g. Open Graph tags) that sits outside the common projection.
     */
    public function describe(FeatureSpec $spec): FeatureDescriptor
    {
        $section = $this->catalog->getSection($spec->section);

        return new FeatureDescriptor(
            section: $spec->section,
            sectionLabel: $spec->sectionLabel ?? (is_string($section['label'] ?? null) ? $section['label'] : null),
            slug: $spec->slug,
            entryLine: $spec->entryLine,
            learnMoreLabel: $spec->learnMoreLabel,
            deepDiveLabel: $spec->deepDiveLabel,
            relatedPayloads: $this->resolveRelatedPayloads($spec->section, $spec->relatedSlugs),
            presentation: $this->documents->resolve(
                $spec->section,
                $spec->slug,
                $spec->fallbackTitle,
                $spec->fallbackSummary,
                $spec->fallbackHighlights,
            ),
            pageTitleSuffix: $spec->pageTitleSuffix,
        );
    }

    /**
     * @param list<string> $slugs
     * @return list<array{href: string, label: string}>
     */
    private function resolveRelatedPayloads(string $section, array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $features = $this->indexFeaturesBySlug($section);
        $resolved = [];

        foreach ($slugs as $slug) {
            $feature = $features[$slug] ?? null;

            $resolved[] = $feature !== null
                ? ['href' => $feature['href'], 'label' => $feature['title']]
                : ['href' => '/demo/' . $section . '/' . $slug, 'label' => $slug];
        }

        return $resolved;
    }

    /**
     * @return array<string, array{slug: string, title: string, href: string}>
     */
    private function indexFeaturesBySlug(string $section): array
    {
        $entry = $this->catalog->getSection($section);

        if ($entry === null) {
            return [];
        }

        $index = [];
        foreach ($entry['features'] ?? [] as $feature) {
            $index[$feature['slug']] = $feature;
        }

        return $index;
    }

    /**
     * @param array{
     *     what?: string|null,
     *     how?: string|null,
     *     why?: string|null,
     *     keywords?: list<string|array{term?: string, title?: string, label?: string, name?: string}>
     * }|null $explanation
     * @return array<string, mixed>
     */
    private function shellContextFor(FeatureDescriptor $feature, ?array $explanation): array
    {
        return [
            'navSections' => $this->catalog->getSections(),
            'featureTree' => $this->catalog->getSidebarTree(),
            'currentSection' => $feature->section,
            'currentSlug' => $feature->slug,
            'infoWhat' => $explanation['what'] ?? $feature->presentation->summary,
            'infoHow' => $explanation['how'] ?? null,
            'infoWhy' => $explanation['why'] ?? null,
            'infoKeywords' => $explanation['keywords'] ?? [],
        ];
    }

    /**
     * @param array{
     *     what?: string|null,
     *     how?: string|null,
     *     why?: string|null,
     *     keywords?: list<string|array{term?: string, title?: string, label?: string, name?: string}>
     * }|null $explanation
     * @return array{
     *     what?: string|null,
     *     how?: string|null,
     *     why?: string|null,
     *     keywords?: list<string|array{term?: string, title?: string, label?: string, name?: string}>
     * }|null
     */
    private function normalizeExplanation(?array $explanation): ?array
    {
        if ($explanation === null) {
            return null;
        }

        $normalized = [];

        foreach (['what', 'how', 'why'] as $key) {
            $value = $explanation[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $normalized[$key] = $value;
            }
        }

        $keywords = $explanation['keywords'] ?? null;
        if (is_array($keywords) && $keywords !== []) {
            $normalized['keywords'] = $keywords;
        }

        return $normalized === [] ? null : $normalized;
    }
}
