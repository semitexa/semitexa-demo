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

    private const NAVIGATION_LAYERS = [
        [
            'key' => 'start-here',
            'label' => 'Start Here',
            'eyebrow' => 'Guided path',
            'summary' => 'The smallest reliable sequence that makes Semitexa feel like a system instead of a brochure.',
            'type' => 'feature-links',
            'href' => '/#start-here',
            'features' => [
                ['section' => 'get-started', 'slug' => 'installation'],
                ['section' => 'get-started', 'slug' => 'local-domain'],
                ['section' => 'get-started', 'slug' => 'module-structure'],
                ['section' => 'get-started', 'slug' => 'base-tenant'],
                ['section' => 'get-started', 'slug' => 'locale-setup'],
                ['section' => 'get-started', 'slug' => 'ai-console'],
                ['section' => 'routing', 'slug' => 'basic'],
                ['section' => 'di', 'slug' => 'overview'],
            ],
        ],
        [
            'key' => 'core-concepts',
            'label' => 'Core Concepts',
            'eyebrow' => 'Learning map',
            'summary' => 'A conceptual map of the framework, grouped by the order a builder or evaluator naturally needs it.',
            'type' => 'section-groups',
            'href' => '/#core-concepts',
            'sectionKeys' => ['get-started', 'routing', 'di', 'data', 'auth', 'events', 'rendering', 'platform', 'api', 'cli', 'llm', 'testing'],
        ],
        [
            'key' => 'full-catalog',
            'label' => 'Full Catalog',
            'eyebrow' => 'Route-first map',
            'summary' => 'The exhaustive live map, still route-first, still real, and still one click away from every feature route.',
            'type' => 'section-groups',
            'href' => '/#full-catalog',
            'sectionKeys' => ['get-started', 'routing', 'di', 'data', 'auth', 'events', 'rendering', 'platform', 'api', 'cli', 'llm', 'testing'],
        ],
    ];

    private const SECTION_GROUPS = [
        'get-started' => [
            [
                'key' => 'structure',
                'label' => 'Module Structure',
                'slugs' => ['module-structure'],
            ],
            [
                'key' => 'onboarding',
                'label' => 'Onboarding',
                'slugs' => ['installation', 'local-domain', 'base-tenant', 'locale-setup', 'ai-console', 'beyond-controllers'],
            ],
        ],
        'routing' => [
            [
                'key' => 'foundations',
                'label' => 'Foundations',
                'slugs' => ['basic', 'parameterized', 'env-route-override'],
            ],
            [
                'key' => 'request-model',
                'label' => 'Request Model',
                'slugs' => ['payload-shield', 'payload-parts'],
            ],
            [
                'key' => 'delivery',
                'label' => 'Delivery',
                'slugs' => ['content-negotiation', 'public-endpoint'],
            ],
        ],
        'di' => [
            [
                'key' => 'container-basics',
                'label' => 'Container Basics',
                'slugs' => ['overview', 'readonly', 'mutable', 'factory', 'contracts'],
            ],
        ],
        'data' => [
            [
                'key' => 'modeling',
                'label' => 'Modeling & Workflow',
                'slugs' => ['domain-models', 'repository-workflow', 'schema-sync'],
            ],
            [
                'key' => 'querying',
                'label' => 'Querying',
                'slugs' => ['query', 'filtering', 'pagination', 'relations', 'table-extension', 'n-plus-one'],
            ],
        ],
        'auth' => [
            [
                'key' => 'identity',
                'label' => 'Identity',
                'slugs' => ['session', 'session-payloads', 'google', 'machine'],
            ],
            [
                'key' => 'access-control',
                'label' => 'Access Control',
                'slugs' => ['protected', 'requires-permission', 'rbac'],
            ],
        ],
        'events' => [
            [
                'key' => 'event-flow',
                'label' => 'Event Flow',
                'slugs' => ['sync', 'deferred', 'queued', 'sse'],
            ],
        ],
        'rendering' => [
            [
                'key' => 'rendering-model',
                'label' => 'SSR Foundation',
                'slugs' => ['philosophy', 'resource-dtos', 'slots', 'components', 'seo', 'assets', 'component-scripts', 'deferred-scripts'],
            ],
            [
                'key' => 'deferred',
                'label' => 'Deferred Delivery',
                'slugs' => ['deferred', 'deferred-encapsulation'],
            ],
            [
                'key' => 'live',
                'label' => 'Reactive UI',
                'slugs' => ['deferred-live', 'reactive-report', 'reactive-import', 'reactive-analytics', 'reactive-ai'],
            ],
        ],
        'platform' => [
            [
                'key' => 'resolution',
                'label' => 'Tenant Resolution',
                'slugs' => ['tenancy-resolution'],
            ],
            [
                'key' => 'configuration',
                'label' => 'Tenant Configuration',
                'slugs' => ['tenancy-config', 'tenancy-layers'],
            ],
            [
                'key' => 'isolation',
                'label' => 'Isolation & Work',
                'slugs' => ['tenancy-isolation', 'tenancy-queue'],
            ],
        ],
        'api' => [
            [
                'key' => 'public-api',
                'label' => 'REST Surface',
                'slugs' => ['rest-api', 'structured-errors', 'active-version', 'sunset-version'],
            ],
            [
                'key' => 'schema',
                'label' => 'Schema Discovery',
                'slugs' => ['schema-discovery', 'graphql', 'rest-graphql'],
            ],
        ],
        'cli' => [
            [
                'key' => 'inspection',
                'label' => 'Describe & Inspect',
                'slugs' => ['describe-commands', 'runtime-maintenance'],
            ],
            [
                'key' => 'automation',
                'label' => 'Automation',
                'slugs' => ['scaffolding-generators', 'workers-scheduling', 'ai-tooling', 'orm-console'],
            ],
        ],
        'llm' => [
            [
                'key' => 'assistant-basics',
                'label' => 'Assistant Surface',
                'slugs' => ['overview', 'providers'],
            ],
            [
                'key' => 'skill-system',
                'label' => 'Skill System',
                'slugs' => ['skills', 'execution-flow'],
            ],
        ],
        'testing' => [
            [
                'key' => 'contracts',
                'label' => 'Contracts',
                'slugs' => ['payload-contracts'],
            ],
        ],
    ];

    private const SECTION_META = [
        'get-started' => [
            'key' => 'get-started',
            'label' => 'Start Here',
            'sidebarLabel' => 'Get Started',
            'summary' => 'The shortest path from fresh scaffold to a trustworthy local Semitexa runtime: install, boot, understand the module map, bind a real host, and reach the first tenant boundary.',
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
            'label' => 'Persistence',
            'summary' => 'Attribute-mapped resources, repositories, filtering, pagination, and relations with real demo data.',
            'icon' => 'DB',
            'eyebrow' => 'Persistence',
            'starter' => true,
            'prerequisites' => [],
        ],
        'auth' => [
            'key' => 'auth',
            'label' => 'Security',
            'summary' => 'Typed session payloads, machine credentials, RBAC, and route protection without string-key auth chaos.',
            'icon' => 'AU',
            'eyebrow' => 'Security',
            'starter' => false,
            'prerequisites' => [],
        ],
        'events' => [
            'key' => 'events',
            'label' => 'Async',
            'summary' => 'Synchronous and deferred event flows, queues, and SSE-style interactions.',
            'icon' => 'EV',
            'eyebrow' => 'Async',
            'starter' => false,
            'prerequisites' => [],
        ],
        'rendering' => [
            'key' => 'rendering',
            'label' => 'UI Rendering & SSR',
            'summary' => 'One rendering story from handler to HTML: page data, page regions, and live updates stay in the same server-driven model instead of splitting into frontend and backend template logic.',
            'icon' => 'UI',
            'eyebrow' => 'Frontend',
            'starter' => false,
            'prerequisites' => [],
        ],
        'platform' => [
            'key' => 'platform',
            'label' => 'Tenancy',
            'summary' => 'Multi-tenant resolution, tenant-aware configuration, and strict isolation of data and background work.',
            'icon' => 'TN',
            'eyebrow' => 'Multi-Tenant',
            'starter' => false,
            'prerequisites' => ['data', 'rendering'],
        ],
        'api' => [
            'key' => 'api',
            'label' => 'API',
            'summary' => 'External API endpoints, machine auth, versioning, and consumer-facing schema behavior.',
            'icon' => 'API',
            'eyebrow' => 'Machine',
            'starter' => false,
            'prerequisites' => ['routing', 'auth'],
        ],
        'cli' => [
            'key' => 'cli',
            'label' => 'CLI',
            'summary' => 'Operational, introspection, and AI-oriented command surfaces that explain and drive the framework from the terminal.',
            'icon' => 'CLI',
            'eyebrow' => 'Operations',
            'starter' => false,
            'prerequisites' => ['routing'],
        ],
        'llm' => [
            'key' => 'llm',
            'label' => 'LLM Module',
            'sidebarLabel' => 'LLM',
            'summary' => 'The dedicated `semitexa/llm` module: AI assistant entrypoint, skill discovery, planner, executor, provider backends, and skill authoring rules.',
            'icon' => 'AI',
            'eyebrow' => 'semitexa/llm',
            'starter' => false,
            'prerequisites' => ['cli'],
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
            $registryEntries = $this->featureRegistry->getBySection($key);
            $featureCount = count($registryEntries);

            if (!$includeEmpty && $featureCount === 0) {
                continue;
            }

            $flatFeatures = $this->mapRegistryEntries($key, $registryEntries);

            $sections[] = array_merge($meta, [
                'featureCount' => $featureCount,
                'href' => '/demo/' . $key,
                'features' => $flatFeatures,
                'groups' => $this->buildSectionGroups($key, $flatFeatures),
            ]);
        }

        return $sections;
    }

    public function getNavigationLayers(): array
    {
        $sections = $this->getSections();
        $sectionsByKey = [];
        foreach ($sections as $section) {
            $sectionsByKey[$section['key']] = $section;
        }

        $layers = [];

        foreach (self::NAVIGATION_LAYERS as $layer) {
            if (($layer['type'] ?? '') === 'feature-links') {
                $features = $this->mapFeatureRefs($layer['features'] ?? []);
                $layers[] = array_merge($layer, [
                    'featureCount' => count($features),
                    'features' => $features,
                ]);
                continue;
            }

            $layerSections = [];
            foreach ($layer['sectionKeys'] ?? [] as $sectionKey) {
                if (isset($sectionsByKey[$sectionKey])) {
                    $layerSections[] = $sectionsByKey[$sectionKey];
                }
            }

            $layers[] = array_merge($layer, [
                'featureCount' => array_sum(array_map(
                    static fn (array $section): int => (int) ($section['featureCount'] ?? 0),
                    $layerSections,
                )),
                'sections' => $layerSections,
            ]);
        }

        return $layers;
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
        return $this->getSidebarTree();
    }

    public function getSidebarTree(): array
    {
        $sidebarLayers = [];

        foreach ($this->getNavigationLayers() as $layer) {
            if (!in_array($layer['key'] ?? '', ['start-here', 'full-catalog'], true)) {
                continue;
            }

            $sidebarLayers[] = $layer;
        }

        return $sidebarLayers;
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
                'slug' => $feature->slug,
                'label' => self::SECTION_META[$section]['label'] ?? ucfirst($section),
                'title' => $feature->title,
                'summary' => $feature->entryLine !== '' ? $feature->entryLine : $feature->summary,
                'opensInNewTab' => $feature->opensInNewTab,
                'href' => $this->featureRegistry->getPath($section, $slug) ?? '/demo/' . $section . '/' . $feature->slug,
            ];
        }

        return null;
    }

    /**
     * @param list<array{section:string,slug:string}> $refs
     * @return list<array<string,mixed>>
     */
    private function mapFeatureRefs(array $refs): array
    {
        $features = [];

        foreach ($refs as $ref) {
            if (!isset($ref['section'], $ref['slug'])) {
                continue;
            }

            $feature = $this->getFeatureCard($ref['section'], $ref['slug']);
            if ($feature !== null) {
                $features[] = $feature;
            }
        }

        return $features;
    }

    /**
     * @param list<array{class:string,attribute:DemoFeature,path:?string}> $entries
     * @return list<array{section:string,slug:string,title:string,summary:string,opensInNewTab:bool,href:string}>
     */
    private function mapRegistryEntries(string $section, array $entries): array
    {
        $features = [];

        foreach ($entries as $entry) {
            if (!isset($entry['attribute']) || !$entry['attribute'] instanceof DemoFeature) {
                continue;
            }

            $feature = $entry['attribute'];
            $features[] = [
                'section' => $section,
                'slug' => $feature->slug,
                'label' => self::SECTION_META[$section]['label'] ?? ucfirst($section),
                'title' => $feature->title,
                'summary' => $feature->entryLine !== '' ? $feature->entryLine : $feature->summary,
                'opensInNewTab' => $feature->opensInNewTab,
                'href' => $entry['path'] ?? '/demo/' . $section . '/' . $feature->slug,
            ];
        }

        return $features;
    }

    /**
     * @param list<array{title:string,slug:string,summary:string,opensInNewTab:bool,href:string}> $features
     * @return list<array{key:string,label:string,featureCount:int,features:list<array<string,mixed>>}>
     */
    private function buildSectionGroups(string $section, array $features): array
    {
        $groups = [];
        $assigned = [];
        $definitions = self::SECTION_GROUPS[$section] ?? [];

        foreach ($definitions as $definition) {
            $groupFeatures = [];
            foreach ($definition['slugs'] as $slug) {
                foreach ($features as $feature) {
                    if ($feature['slug'] !== $slug || isset($assigned[$slug])) {
                        continue;
                    }

                    $groupFeatures[] = $feature;
                    $assigned[$slug] = true;
                    break;
                }
            }

            if ($groupFeatures === []) {
                continue;
            }

            $groups[] = [
                'key' => $definition['key'],
                'label' => $definition['label'],
                'featureCount' => count($groupFeatures),
                'features' => $groupFeatures,
            ];
        }

        $remaining = array_values(array_filter(
            $features,
            static fn (array $feature): bool => !isset($assigned[$feature['slug']]),
        ));

        if ($remaining !== []) {
            $groups[] = [
                'key' => $section . '-overview',
                'label' => 'Overview',
                'featureCount' => count($remaining),
                'features' => $remaining,
            ];
        }

        return $groups;
    }
}
