<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attribute\AsComponent;

/**
 * Section heading with title, description, and feature count badge.
 */
#[AsComponent(
    name: 'demo-section-header',
    template: '@project-layouts-semitexa-demo/components/section-header.html.twig',
)]
final class SectionHeaderComponent
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly int $featureCount,
    ) {}
}
