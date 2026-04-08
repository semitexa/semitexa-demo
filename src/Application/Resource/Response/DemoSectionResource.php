<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'demo_section',
    template: '@project-layouts-semitexa-demo/pages/section.html.twig',
)]
class DemoSectionResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    public function withSection(string $section): self
    {
        return $this->with('section', $section);
    }

    public function withSectionLabel(string $label): self
    {
        return $this->with('sectionLabel', $label);
    }

    public function withSectionIcon(string $icon): self
    {
        return $this->with('sectionIcon', $icon);
    }

    public function withSectionSummary(string $summary): self
    {
        $this->seoTag('description', $summary);

        return $this->with('sectionSummary', $summary);
    }

    public function withGroups(array $groups): self
    {
        return $this->with('groups', $groups);
    }

    public function withFeatures(array $features): self
    {
        $keywords = [$this->getRenderContext()['sectionLabel'] ?? null];
        foreach ($features as $feature) {
            if (is_array($feature) && isset($feature['title']) && is_string($feature['title'])) {
                $keywords[] = $feature['title'];
            }
        }
        $this->seoKeywords(array_values(array_filter($keywords, static fn ($value): bool => is_string($value) && $value !== '')));

        return $this->with('features', $features);
    }
}
