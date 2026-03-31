<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attributes\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'demo_home',
    template: '@project-layouts-semitexa-demo/pages/home.html.twig',
)]
class DemoHomeResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    public function withHomeCatalog(array $context): self
    {
        $resource = $this;

        if (array_key_exists('sections', $context)) {
            $resource = $resource->withSections($context['sections']);
        }
        if (array_key_exists('starterSections', $context)) {
            $resource = $resource->withStarterSections($context['starterSections']);
        }
        if (array_key_exists('featuredFeatures', $context)) {
            $resource = $resource->withFeaturedFeatures($context['featuredFeatures']);
        }
        if (array_key_exists('totalFeatureCount', $context)) {
            $resource = $resource->withTotalFeatureCount((int) $context['totalFeatureCount']);
        }
        if (array_key_exists('getStartedGuide', $context)) {
            $resource = $resource->withGetStartedGuide($context['getStartedGuide']);
        }

        return $resource;
    }

    public function withSections(array $sections): self
    {
        return $this->with('sections', $sections);
    }

    public function withStarterSections(array $starterSections): self
    {
        return $this->with('starterSections', $starterSections);
    }

    public function withTotalFeatureCount(int $count): self
    {
        return $this->with('totalFeatureCount', $count);
    }

    public function withFeaturedFeatures(array $features): self
    {
        return $this->with('featuredFeatures', $features);
    }

    public function withRelease(array $release): self
    {
        return $this->with('release', $release);
    }

    public function withGetStartedGuide(array $guide): self
    {
        return $this->with('getStartedGuide', $guide);
    }
}
