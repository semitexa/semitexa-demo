<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attributes\AsComponent;

#[AsComponent(
    name: 'demo-script-signal-card',
    template: '@project-layouts-semitexa-demo/components/script-signal-card.html.twig',
    cacheable: false,
    script: 'semitexa-demo:js:script-signal-card',
)]
final class ScriptSignalCardComponent
{
    public function __construct(
        public readonly string $eyebrow,
        public readonly string $title,
        public readonly string $summary,
        public readonly string $buttonLabel = 'Activate enhancement',
    ) {}
}
