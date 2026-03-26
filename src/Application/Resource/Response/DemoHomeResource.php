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
}
