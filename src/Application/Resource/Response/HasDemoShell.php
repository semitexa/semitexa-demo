<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

trait HasDemoShell
{
    public function withDemoShellContext(array $context): static
    {
        $navMode = $context['navMode'] ?? null;

        $activeLayerKey = $context['activeLayerKey'] ?? null;
        if (!is_string($activeLayerKey) || $activeLayerKey === '') {
            $currentSection = $context['currentSection'] ?? null;
            if (!is_string($currentSection) || $currentSection === '' || $currentSection === 'get-started') {
                $activeLayerKey = 'start-here';
            } else {
                $activeLayerKey = 'full-catalog';
            }
        }

        if (!is_string($navMode) || $navMode === '') {
            $navMode = $activeLayerKey === 'start-here' ? 'guided' : 'catalog';
        }

        $shellContext = [];

        foreach ([
            'navSections',
            'navMode',
            'activeLayerKey',
            'featureTree',
            'currentSection',
            'currentSlug',
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

        return $this->setRenderContext(array_merge($this->getRenderContext(), $shellContext));
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
}
