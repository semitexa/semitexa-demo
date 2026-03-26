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
        return $this->setRenderContext(array_merge($this->getRenderContext(), [
            'sections' => $context['sections'],
            'starterSections' => $context['starterSections'],
            'featuredFeatures' => $context['featuredFeatures'],
            'totalFeatureCount' => $context['totalFeatureCount'],
        ]));
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
}
