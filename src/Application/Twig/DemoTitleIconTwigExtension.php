<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Twig;

use Semitexa\Ssr\Attributes\AsTwigExtension;
use Semitexa\Ssr\Extension\TwigExtensionRegistry;
use Twig\Markup;

#[AsTwigExtension]
final class DemoTitleIconTwigExtension
{
    private const SECTION_VARIANTS = [
        'routing' => 'routing',
        'di' => 'container',
        'data' => 'data',
        'auth' => 'security',
        'events' => 'async',
        'rendering' => 'rendering',
        'platform' => 'platform',
        'api' => 'api',
        'testing' => 'testing',
    ];

    public function registerFunctions(): void
    {
        TwigExtensionRegistry::registerFunction(
            'demo_title_icon',
            [$this, 'renderTitleIcon'],
            ['is_safe' => ['html']],
        );
    }

    public function renderTitleIcon(string $title, ?string $section = null, ?string $slug = null): Markup
    {
        $context = strtolower(trim($title . ' ' . ($section ?? '') . ' ' . ($slug ?? '')));
        $icon = $this->resolveIcon($context, $section, $slug);
        $variant = $icon === 'shield'
            ? 'shield'
            : (self::SECTION_VARIANTS[$section ?? ''] ?? 'generic');

        $html = sprintf(
            '<span class="feature-detail__title-icon feature-detail__title-icon--%s" aria-hidden="true">%s</span>',
            htmlspecialchars($variant, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $this->iconSvg($icon),
        );

        return new Markup($html, 'UTF-8');
    }

    private function resolveIcon(string $context, ?string $section, ?string $slug): string
    {
        if (str_contains($context, 'payload') && (str_contains($context, 'shield') || str_contains($context, 'boundary'))) {
            return 'shield';
        }

        $keywordIcons = [
            'shield' => 'shield',
            'auth' => 'lock',
            'permission' => 'lock',
            'protected' => 'lock',
            'security' => 'lock',
            'rbac' => 'lock',
            'session' => 'key',
            'machine' => 'key',
            'route' => 'route',
            'routing' => 'route',
            'endpoint' => 'route',
            'negotiation' => 'switch',
            'parts' => 'blocks',
            'component' => 'blocks',
            'slot' => 'blocks',
            'block' => 'blocks',
            'deferred' => 'spark',
            'reactive' => 'spark',
            'live' => 'spark',
            'stream' => 'pulse',
            'sse' => 'pulse',
            'event' => 'pulse',
            'queue' => 'pulse',
            'async' => 'pulse',
            'query' => 'funnel',
            'filter' => 'funnel',
            'pagination' => 'funnel',
            'relation' => 'nodes',
            'schema' => 'database',
            'orm' => 'database',
            'repository' => 'database',
            'table' => 'database',
            'domain' => 'layers',
            'model' => 'layers',
            'contract' => 'layers',
            'injection' => 'plug',
            'factory' => 'plug',
            'readonly' => 'plug',
            'mutable' => 'plug',
            'tenant' => 'building',
            'platform' => 'building',
            'config' => 'sliders',
            'api' => 'brackets',
            'consumer' => 'brackets',
            'profile' => 'brackets',
            'resource dto' => 'document',
            'dto' => 'document',
            'seo' => 'globe',
            'asset' => 'package',
            'script' => 'terminal',
            'console' => 'terminal',
            'cli' => 'terminal',
            'test' => 'flask',
            'toolkit' => 'flask',
            'ai' => 'stars',
            'analytics' => 'chart',
            'report' => 'chart',
            'import' => 'arrow',
        ];

        foreach ($keywordIcons as $keyword => $icon) {
            if (str_contains($context, $keyword)) {
                return $icon;
            }
        }

        return match ($section) {
            'routing' => 'route',
            'di' => 'plug',
            'data' => 'database',
            'auth' => 'lock',
            'events' => 'pulse',
            'rendering' => 'blocks',
            'platform' => 'building',
            'api' => 'brackets',
            'testing' => 'flask',
            default => $slug === 'payload-shield' ? 'shield' : 'stars',
        };
    }

    private function iconSvg(string $icon): string
    {
        return match ($icon) {
            'shield' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M12 2.5 4.5 5.5v5.84c0 4.64 2.84 8.88 7.5 10.66 4.66-1.78 7.5-6.02 7.5-10.66V5.5L12 2.5Zm0 2.16 5.5 2.2v4.48c0 3.73-2.2 7.16-5.5 8.74-3.3-1.58-5.5-5.01-5.5-8.74V6.86L12 4.66Z" fill="currentColor"/><path d="m10.96 14.8-2.58-2.58 1.42-1.42 1.16 1.17 3.24-3.25 1.42 1.42-4.66 4.66Z" fill="currentColor"/></svg>',
            'lock' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M7 10V8a5 5 0 1 1 10 0v2h1.25A1.75 1.75 0 0 1 20 11.75v7.5A1.75 1.75 0 0 1 18.25 21h-12.5A1.75 1.75 0 0 1 4 19.25v-7.5A1.75 1.75 0 0 1 5.75 10H7Zm2 0h6V8a3 3 0 1 0-6 0v2Zm3 3a1.5 1.5 0 0 1 .75 2.8V18h-1.5v-2.2A1.5 1.5 0 0 1 12 13Z" fill="currentColor"/></svg>',
            'key' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M14.5 4a5.5 5.5 0 1 1-4.74 8.29L4 18.05V20h2v-1.5h1.5V17H9v-1.5h1.95l1.04-1.04A5.48 5.48 0 0 1 14.5 4Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Z" fill="currentColor"/></svg>',
            'route' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M7 5a3 3 0 1 1-.001 6.001A3 3 0 0 1 7 5Zm0 2a1 1 0 1 0 .001 2.001A1 1 0 0 0 7 7Zm10 6a3 3 0 1 1-.001 6.001A3 3 0 0 1 17 13Zm0 2a1 1 0 1 0 .001 2.001A1 1 0 0 0 17 15ZM8 8h4a4 4 0 0 1 4 4v2h-2v-2a2 2 0 0 0-2-2H8V8Z" fill="currentColor"/></svg>',
            'switch' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M7 7h8.59l-2.3-2.29L14.7 3.3 19.4 8l-4.7 4.7-1.41-1.41L15.59 9H7V7Zm10 10H8.41l2.3 2.29-1.42 1.41L4.6 16l4.69-4.7 1.42 1.41L8.41 15H17v2Z" fill="currentColor"/></svg>',
            'blocks' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M4 4h7v7H4V4Zm9 0h7v4h-7V4ZM13 10h7v10h-7V10ZM4 13h7v7H4v-7Z" fill="currentColor"/></svg>',
            'spark' => '<svg viewBox="0 0 24 24" focusable="false"><path d="m12 2 1.9 5.1L19 9l-5.1 1.9L12 16l-1.9-5.1L5 9l5.1-1.9L12 2Zm7 13 1 2.5L22.5 19 20 20l-1 2.5L18 20l-2.5-1 2.5-1.5 1-2.5ZM6 14l1.2 3.2L10.5 18l-3.3 1.2L6 22.5l-1.2-3.3L1.5 18l3.3-.8L6 14Z" fill="currentColor"/></svg>',
            'pulse' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M3 12h4l2-4 4 8 2-4h6v2h-7.24L13 18l-4-8-1 2H3v-2Z" fill="currentColor"/></svg>',
            'database' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M12 4c-4.42 0-8 1.34-8 3v10c0 1.66 3.58 3 8 3s8-1.34 8-3V7c0-1.66-3.58-3-8-3Zm0 2c3.9 0 6 .99 6 1s-2.1 1-6 1-6-.99-6-1 2.1-1 6-1Zm0 12c-3.9 0-6-.99-6-1v-2.06c1.51.68 3.76 1.06 6 1.06s4.49-.38 6-1.06V17c0 .01-2.1 1-6 1Zm0-5c-3.9 0-6-.99-6-1V9.94c1.51.68 3.76 1.06 6 1.06s4.49-.38 6-1.06V12c0 .01-2.1 1-6 1Z" fill="currentColor"/></svg>',
            'funnel' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z" fill="currentColor"/></svg>',
            'nodes' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M6 5a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm12 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4ZM12 15a2 2 0 1 1 0 4 2 2 0 0 1 0-4ZM7.7 8.35l2.92 6.3-1.82.84-2.92-6.3 1.82-.84Zm8.6 0 1.82.84-2.92 6.3-1.82-.84 2.92-6.3ZM8 6h8v2H8V6Z" fill="currentColor"/></svg>',
            'layers' => '<svg viewBox="0 0 24 24" focusable="false"><path d="m12 3 8 4-8 4-8-4 8-4Zm-8 8 8 4 8-4v3l-8 4-8-4v-3Zm0 6 8 4 8-4v3l-8 4-8-4v-3Z" fill="currentColor"/></svg>',
            'plug' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M10 3h2v6h2V3h2v6a4 4 0 0 1-4 4v3h3v2h-3v3h-2v-3H7v-2h3v-3a4 4 0 0 1-4-4V3h2v6h2V3Z" fill="currentColor"/></svg>',
            'building' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M4 20V6l8-3 8 3v14h-6v-5h-4v5H4Zm4-9h2V9H8v2Zm0 4h2v-2H8v2Zm6-4h2V9h-2v2Zm0 4h2v-2h-2v2Z" fill="currentColor"/></svg>',
            'sliders' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M5 7h5v2H5V7Zm9 0h5v2h-5V7Zm-2-2h2v6h-2V5ZM5 15h9v2H5v-2Zm13 0h1v2h-1v-2Zm-2-2h2v6h-2v-6Z" fill="currentColor"/></svg>',
            'brackets' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M8 5H6v14h2v2H4V3h4v2Zm8 0V3h4v18h-4v-2h2V5h-2Zm-5 3h2v8h-2V8Z" fill="currentColor"/></svg>',
            'document' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M7 3h7l5 5v13H7V3Zm7 1.5V9h4.5L14 4.5ZM9 12h6v2H9v-2Zm0 4h6v2H9v-2Z" fill="currentColor"/></svg>',
            'globe' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18Zm5.66 8a7.03 7.03 0 0 0-3.03-5.08c.4 1.18.67 2.56.75 4.08h2.28Zm-4.29 0c-.12-1.75-.49-3.28-1.02-4.42A7.02 7.02 0 0 0 7.34 11h6.03Zm-6.03 2a7.02 7.02 0 0 0 5.01 4.42c.53-1.14.9-2.67 1.02-4.42H7.34Zm8.04 0c-.08 1.52-.35 2.9-.75 4.08A7.03 7.03 0 0 0 17.66 13h-2.28Z" fill="currentColor"/></svg>',
            'package' => '<svg viewBox="0 0 24 24" focusable="false"><path d="m12 2 8 4v12l-8 4-8-4V6l8-4Zm0 2.24L7.24 6.62 12 9l4.76-2.38L12 4.24ZM6 8.24V16.8l5 2.5v-8.56L6 8.24Zm7 11.06 5-2.5V8.24l-5 2.5v8.56Z" fill="currentColor"/></svg>',
            'terminal' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 2v10h16V7H4Zm2.2 2.2L9 12l-2.8 2.8 1.4 1.4L11.8 12 7.6 7.8 6.2 9.2ZM12 15h5v-2h-5v2Z" fill="currentColor"/></svg>',
            'flask' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M9 3h6v2l-1 1v3.38l4.95 7.43A2 2 0 0 1 17.3 20H6.7a2 2 0 0 1-1.65-3.19L10 9.38V6L9 5V3Zm2.4 7.2-4.7 7.05c-.09.13 0 .35.17.35h10.26c.17 0 .26-.22.17-.35l-4.7-7.05H11.4Z" fill="currentColor"/></svg>',
            'stars' => '<svg viewBox="0 0 24 24" focusable="false"><path d="m12 3 1.55 4.45L18 9l-4.45 1.55L12 15l-1.55-4.45L6 9l4.45-1.55L12 3Zm7 11 1 2.5 2.5 1-2.5 1L19 21l-1-2.5-2.5-1 2.5-1 1-2.5ZM5 14l1 2.5 2.5 1-2.5 1L5 21l-1-2.5-2.5-1 2.5-1L5 14Z" fill="currentColor"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M5 19V9h3v10H5Zm5 0V5h3v14h-3Zm5 0v-7h3v7h-3Z" fill="currentColor"/></svg>',
            'arrow' => '<svg viewBox="0 0 24 24" focusable="false"><path d="M12 4 20 12l-8 8-1.41-1.41L16.17 13H4v-2h12.17l-5.58-5.59L12 4Z" fill="currentColor"/></svg>',
            default => '<svg viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="8" fill="currentColor"/></svg>',
        };
    }
}
