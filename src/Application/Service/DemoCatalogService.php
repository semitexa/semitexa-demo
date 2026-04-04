<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Attributes\DemoFeature;

#[AsService]
final class DemoCatalogService
{
    #[InjectAsReadonly]
    protected DemoFeatureRegistry $featureRegistry;

    private const SECTION_META = [
        'get-started' => [
            'key' => 'get-started',
            'label' => 'Get Started',
            'summary' => 'The shortest path from fresh scaffold to a trustworthy local Semitexa runtime: install, boot, bind a real host, and understand the first tenant boundary.',
            'icon' => 'GO',
            'eyebrow' => 'Onboarding',
            'starter' => true,
            'prerequisites' => [],
        ],
        'routing' => [
            'key' => 'routing',
            'label' => 'Routing & Handlers',
            'summary' => 'Attribute-driven routes, typed handlers, and content negotiation in one coherent request pipeline.',
            'icon' => 'RT',
            'eyebrow' => 'HTTP Layer',
            'starter' => true,
            'prerequisites' => [],
        ],
        'di' => [
            'key' => 'di',
            'label' => 'Dependency Injection',
            'summary' => 'Single-path DI with explicit lifecycles, deterministic contracts, and stable boot behavior for long-running workers.',
            'icon' => 'DI',
            'eyebrow' => 'Container',
            'starter' => true,
            'prerequisites' => [],
        ],
        'data' => [
            'key' => 'data',
            'label' => 'Data & Persistence',
            'summary' => 'Attribute-mapped resources, repositories, filtering, pagination, and relations with real demo data.',
            'icon' => 'DB',
            'eyebrow' => 'Persistence',
            'starter' => true,
            'prerequisites' => [],
        ],
        'auth' => [
            'key' => 'auth',
            'label' => 'Auth & Security',
            'summary' => 'Typed session payloads, machine credentials, RBAC, and route protection without string-key auth chaos.',
            'icon' => 'AU',
            'eyebrow' => 'Security',
            'starter' => false,
            'prerequisites' => [],
        ],
        'events' => [
            'key' => 'events',
            'label' => 'Events & Async',
            'summary' => 'Synchronous and deferred event flows, queues, and SSE-style interactions.',
            'icon' => 'EV',
            'eyebrow' => 'Async',
            'starter' => false,
            'prerequisites' => [],
        ],
        'rendering' => [
            'key' => 'rendering',
            'label' => 'Rendering & SSR',
            'summary' => 'One rendering story from handler to HTML: page data, page regions, and live updates stay in the same server-driven model instead of splitting into frontend and backend template logic.',
            'icon' => 'UI',
            'eyebrow' => 'Frontend',
            'starter' => false,
            'prerequisites' => [],
        ],
        'platform' => [
            'key' => 'platform',
            'label' => 'Tenancy & Isolation',
            'summary' => 'Multi-tenant resolution, tenant-aware configuration, and strict isolation of data and background work.',
            'icon' => 'TN',
            'eyebrow' => 'Multi-Tenant',
            'starter' => false,
            'prerequisites' => ['data', 'rendering'],
        ],
        'api' => [
            'key' => 'api',
            'label' => 'Intelligent API',
            'summary' => 'External API endpoints, machine auth, versioning, and consumer-facing schema behavior.',
            'icon' => 'API',
            'eyebrow' => 'Machine',
            'starter' => false,
            'prerequisites' => ['routing', 'auth'],
        ],
        'cli' => [
            'key' => 'cli',
            'label' => 'CLI Commands',
            'summary' => 'Operational, introspection, and AI-oriented command surfaces that explain and drive the framework from the terminal.',
            'icon' => 'CLI',
            'eyebrow' => 'Operations',
            'starter' => false,
            'prerequisites' => ['routing'],
        ],
        'testing' => [
            'key' => 'testing',
            'label' => 'Testing',
            'summary' => 'Contract-level verification patterns for payloads and other framework boundaries.',
            'icon' => 'QA',
            'eyebrow' => 'Verification',
            'starter' => false,
            'prerequisites' => ['routing'],
        ],
    ];

    private const FEATURED_FEATURES = [
        ['section' => 'rendering', 'slug' => 'deferred'],
        ['section' => 'rendering', 'slug' => 'reactive-ai'],
        ['section' => 'platform', 'slug' => 'tenancy-resolution'],
    ];

    public function getSections(bool $includeEmpty = false): array
    {
        $sections = [];

        foreach (self::SECTION_META as $key => $meta) {
            $features = $this->featureRegistry->getBySection($key);
            $featureCount = count($features);

            if (!$includeEmpty && $featureCount === 0) {
                continue;
            }

            $sections[] = array_merge($meta, [
                'featureCount' => $featureCount,
                'href' => '/demo/' . $key,
                'features' => array_map(
                    static fn (array $entry): array => [
                        'title' => $entry['attribute']->title,
                        'slug' => $entry['attribute']->slug,
                        'summary' => $entry['attribute']->summary,
                        'opensInNewTab' => $entry['attribute']->opensInNewTab,
                        'href' => $entry['path'] ?? '/demo/' . $entry['attribute']->section . '/' . $entry['attribute']->slug,
                    ],
                    $features,
                ),
            ]);
        }

        return $sections;
    }

    public function getStarterSections(): array
    {
        return array_values(array_filter(
            $this->getSections(),
            static fn (array $section): bool => $section['starter'] === true,
        ));
    }

    public function getNavSections(): array
    {
        return array_map(
            static fn (array $section): array => [
                'key' => $section['key'],
                'label' => $section['label'],
                'icon' => $section['icon'],
                'eyebrow' => $section['eyebrow'],
                'featureCount' => $section['featureCount'],
                'href' => $section['href'],
            ],
            $this->getSections(),
        );
    }

    public function getSection(string $section): ?array
    {
        foreach ($this->getSections(includeEmpty: true) as $entry) {
            if ($entry['key'] === $section) {
                return $entry;
            }
        }

        return null;
    }

    public function getFeatureTree(): array
    {
        return $this->getSections();
    }

    public function getFeatureTreeForSection(string $section): array
    {
        $entry = $this->getSection($section);

        return $entry === null ? [] : [$entry];
    }

    public function getFeaturedFeatures(): array
    {
        $featured = [];

        foreach (self::FEATURED_FEATURES as $candidate) {
            $feature = $this->getFeatureCard($candidate['section'], $candidate['slug']);
            if ($feature !== null) {
                $featured[] = $feature;
            }
        }

        if ($featured !== []) {
            return $featured;
        }

        foreach ($this->getSections() as $section) {
            foreach ($section['features'] as $feature) {
                $featured[] = array_merge($feature, [
                    'section' => $section['key'],
                    'label' => $section['label'],
                ]);

                if (count($featured) === 3) {
                    return $featured;
                }
            }
        }

        return $featured;
    }

    public function getTotalFeatureCount(): int
    {
        return array_sum(array_map(
            static fn (array $section): int => $section['featureCount'],
            $this->getSections(),
        ));
    }

    public function buildInfoPanel(?array $explanation = null, ?string $fallbackWhat = null): array
    {
        return [
            'what' => $explanation['what'] ?? $fallbackWhat,
            'how' => $explanation['how'] ?? null,
            'why' => $explanation['why'] ?? null,
            'keywords' => $explanation['keywords'] ?? [],
        ];
    }

    private function getFeatureCard(string $section, string $slug): ?array
    {
        foreach ($this->featureRegistry->getBySection($section) as $entry) {
            /** @var DemoFeature $feature */
            $feature = $entry['attribute'];
            if ($feature->slug !== $slug) {
                continue;
            }

            return [
                'section' => $section,
                'label' => self::SECTION_META[$section]['label'] ?? ucfirst($section),
                'title' => $feature->title,
                'summary' => $feature->entryLine !== '' ? $feature->entryLine : $feature->summary,
                'opensInNewTab' => $feature->opensInNewTab,
                'href' => $this->featureRegistry->getPath($section, $slug) ?? '/demo/' . $section . '/' . $feature->slug,
            ];
        }

        return null;
    }
}
