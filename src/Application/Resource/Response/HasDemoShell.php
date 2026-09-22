<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Auth\Context\AuthManager;
use Semitexa\Demo\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Service\DemoAuthMode;

trait HasDemoShell
{
    public function withDemoShellContext(array $context): static
    {
        $navMode = $context['navMode'] ?? null;

        $activeLayerKey = $context['activeLayerKey'] ?? null;
        if (!is_string($activeLayerKey) || $activeLayerKey === '') {
            $currentSection = $context['currentSection'] ?? null;
            if (is_string($currentSection) && $currentSection === 'get-started') {
                $activeLayerKey = 'start-here';
            } else {
                $activeLayerKey = 'full-catalog';
            }
        }

        if (!is_string($navMode) || $navMode === '') {
            $navMode = 'catalog';
        }

        $shellContext = [];

        foreach ([
            'navSections',
            'navMode',
            'activeLayerKey',
            'featureTree',
            'docsSearchIndex',
            'currentSection',
            'currentSlug',
            'authUi',
            'infoWhat',
            'infoHow',
            'infoWhy',
            'infoKeywords',
        ] as $key) {
            if (array_key_exists($key, $context)) {
                $shellContext[$key] = $context[$key];
            }
        }

        if (array_key_exists('infoWhat', $context) && is_string($context['infoWhat']) && $context['infoWhat'] !== '') {
            $this->seoTagDefault('description', $context['infoWhat']);
        }

        if (array_key_exists('infoWhat', $context) && is_string($context['infoWhat']) && $context['infoWhat'] !== '') {
            $this->seoTagDefault('og:description', $context['infoWhat']);
        }

        if (array_key_exists('infoKeywords', $context) && is_array($context['infoKeywords']) && $context['infoKeywords'] !== []) {
            /** @var array<int, string|array{term?: string, title?: string, label?: string, name?: string}> $infoKeywords */
            $infoKeywords = $context['infoKeywords'];
            $this->seoKeywords($this->normalizeSeoKeywordTerms($infoKeywords));
        }

        $this->seoTagDefault('robots', 'index,follow');
        $this->seoTagDefault('og:type', 'website');

        $shellContext['navMode'] = $navMode;
        $shellContext['activeLayerKey'] = $activeLayerKey;
        $shellContext['authUi'] = $shellContext['authUi'] ?? $this->buildAuthUiContext();
        $shellContext['docsSearchIndex'] = $shellContext['docsSearchIndex']
            ?? $this->buildDocsSearchIndex($shellContext['navSections'] ?? []);

        return $this->setRenderContext(array_merge($this->getRenderContext(), $shellContext));
    }

    /**
     * @param list<array<string, mixed>> $sections
     * @return list<array{title: string, section: string, summary: string, href: string, searchText: string}>
     */
    private function buildDocsSearchIndex(array $sections): array
    {
        $index = [];

        foreach ($sections as $section) {
            $sectionKey = trim((string) ($section['key'] ?? ''));
            $sectionLabel = trim((string) ($section['label'] ?? $sectionKey));
            $sectionHref = trim((string) ($section['href'] ?? ''));
            $sectionSummary = trim((string) ($section['summary'] ?? ''));

            if ($sectionLabel !== '' && $sectionHref !== '') {
                $index[] = $this->docsSearchItem(
                    title: $sectionLabel,
                    section: 'Section',
                    summary: $sectionSummary,
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

                $index[] = $this->docsSearchItem(
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
    private function docsSearchItem(string $title, string $section, string $summary, string $href, array $terms): array
    {
        return [
            'title' => $title,
            'section' => $section,
            'summary' => $summary,
            'href' => $href,
            'searchText' => mb_strtolower(implode(' ', [$title, $section, $summary, ...$terms])),
        ];
    }

    public function withNavSections(array $sections): static
    {
        return $this->with('navSections', $sections);
    }

    public function withFeatureTree(array $featureTree): static
    {
        return $this->with('featureTree', $featureTree);
    }

    public function withCurrentSection(?string $section): static
    {
        return $this->with('currentSection', $section);
    }

    public function withCurrentSlug(?string $slug): static
    {
        return $this->with('currentSlug', $slug);
    }

    public function withInfoPanel(?string $what, ?string $how = null, ?string $why = null, array $keywords = []): static
    {
        return $this
            ->with('infoWhat', $what)
            ->with('infoHow', $how)
            ->with('infoWhy', $why)
            ->with('infoKeywords', $keywords);
    }

    /**
     * @param array<int, string|array{term?: string, title?: string, label?: string, name?: string}> $keywords
     * @return list<string>
     */
    private function normalizeSeoKeywordTerms(array $keywords): array
    {
        $terms = [];

        foreach ($keywords as $keyword) {
            if (is_string($keyword) && $keyword !== '') {
                $terms[] = $keyword;
                continue;
            }

            if (!is_array($keyword)) {
                continue;
            }

            foreach (['term', 'title', 'label', 'name'] as $key) {
                if (isset($keyword[$key]) && is_string($keyword[$key]) && $keyword[$key] !== '') {
                    $terms[] = $keyword[$key];
                    break;
                }
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * @return array{
     *   isAuthenticated: bool,
     *   label: string,
     *   shortLabel: string,
     *   email: ?string,
     *   startUrl: string,
     *   authPageUrl: string,
     *   accountUrl: string,
     *   logoutUrl: string
     * }
     */
    private function buildAuthUiContext(): array
    {
        $auth = AuthManager::getInstance();
        $user = $auth->getUser();
        $googleUser = $user instanceof GooglePrincipal ? $user : null;
        $returnTo = $this->resolveAuthReturnTo();
        $startUrl = '/demo/auth/google/start?return_to=' . rawurlencode($returnTo);
        $authPageUrl = '/demo/auth/google?return_to=' . rawurlencode($returnTo);
        $label = $googleUser?->getDisplayName() ?? ($user?->getId() ?? 'Google user');

        return [
            'isAuthenticated' => !$auth->isGuest(),
            'label' => $label,
            'shortLabel' => $this->shortenAuthLabel($label),
            'email' => $googleUser?->getEmail(),
            'startUrl' => $startUrl,
            'authPageUrl' => $authPageUrl,
            'accountUrl' => $authPageUrl,
            'logoutUrl' => '/demo/auth/google/logout?return_to=' . rawurlencode($returnTo),
            'actionLabel' => DemoAuthMode::actionLabel(),
            'signInTitle' => DemoAuthMode::signInTitle(),
            'signedInLabel' => DemoAuthMode::signedInLabel(),
        ];
    }

    private function resolveAuthReturnTo(): string
    {
        $requestUri = trim((string) ($_SERVER['REQUEST_URI'] ?? ''));

        if ($requestUri === '' || !str_starts_with($requestUri, '/')) {
            return '/demo';
        }

        if (preg_match('/[\r\n]/', $requestUri) === 1) {
            return '/demo';
        }

        return $requestUri;
    }

    private function shortenAuthLabel(string $label): string
    {
        $label = trim($label);
        if ($label === '') {
            return 'there';
        }

        $parts = preg_split('/\s+/', $label);
        $short = is_array($parts) ? trim((string) ($parts[0] ?? $label)) : $label;

        if ($short === '') {
            return 'there';
        }

        return mb_substr($short, 0, 18);
    }
}
