<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

trait HasDemoShell
{
    public function withDemoShellContext(array $context): static
    {
        $shellContext = [];

        foreach ([
            'navSections',
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

        if (array_key_exists('infoKeywords', $context) && is_array($context['infoKeywords']) && $context['infoKeywords'] !== []) {
            /** @var array<int, string|array{term?: string, title?: string, label?: string, name?: string}> $infoKeywords */
            $infoKeywords = $context['infoKeywords'];
            $this->seoKeywords($this->normalizeSeoKeywordTerms($infoKeywords));
        }

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
