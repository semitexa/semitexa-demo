<?php

declare(strict_types=1);

namespace App\Application\Component;

use Semitexa\Ssr\Attributes\AsComponent;

#[AsComponent(
    name: 'disclosure-prompt',
    template: '@app/components/disclosure-prompt.html.twig',
    cacheable: false,
)]
final class DisclosurePromptComponent
{
    public function __construct(
        public readonly string $label,
        public readonly string $target,
    ) {}
}
