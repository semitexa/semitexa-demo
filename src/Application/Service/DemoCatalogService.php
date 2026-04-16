<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Docs\Application\Service\DocumentManifestBuilder;

#[AsService]
final class DemoCatalogService
{
    #[InjectAsReadonly]
    protected DocumentManifestBuilder $documentManifestBuilder;

    /**
     * Feature sections whose title/summary are now owned by Semitexa Docs.
     *
     * @var list<string>
     */
    private const DOCS_BACKED_SECTIONS = [
        'get-started',
        'project-graph',
        'routing',
        'di',
        'data',
        'auth',
        'llm',
        'platform',
        'cli',
        'testing',
        'events',
        'api',
        'rendering',
    ];

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
                ['section' => 'project-graph', 'slug' => 'overview'],
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
            'sectionKeys' => ['get-started', 'routing', 'di', 'data', 'auth', 'events', 'rendering', 'platform', 'api', 'cli', 'project-graph', 'llm', 'testing'],
        ],
        [
            'key' => 'full-catalog',
            'label' => 'Full Catalog',
            'eyebrow' => 'Route-first map',
            'summary' => 'The exhaustive live map, still route-first, still real, and still one click away from every feature route.',
            'type' => 'section-groups',
            'href' => '/#full-catalog',
            'sectionKeys' => ['get-started', 'routing', 'di', 'data', 'auth', 'events', 'rendering', 'platform', 'api', 'cli', 'project-graph', 'llm', 'testing'],
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
                'slugs' => ['arena', 'sync', 'deferred', 'queued', 'sse'],
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
        'project-graph' => [
            [
                'key' => 'launch',
                'label' => 'Start Here',
                'slugs' => ['overview'],
            ],
            [
                'key' => 'exploration',
                'label' => 'Explore & Inspect',
                'slugs' => ['inspection'],
            ],
            [
                'key' => 'change-safety',
                'label' => 'Impact & Context',
                'slugs' => ['impact'],
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
        'project-graph' => [
            'key' => 'project-graph',
            'label' => 'Project Graph',
            'summary' => 'A live structural map of the codebase for AI agents and engineers: generate the graph once, inspect dependencies fast, and ask impact questions before risky edits.',
            'icon' => 'PG',
            'eyebrow' => 'AI Accelerator',
            'starter' => true,
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

    /**
     * Static feature metadata keyed by "section/slug".
     *
     * Title and summary for each feature. Order is implicit from the
     * position inside SECTION_GROUPS. The only feature that sets
     * opensInNewTab is rendering/deferred.
     *
     * @var array<string, array{title: string, summary: string, opensInNewTab?: true}>
     */
    private const FEATURE_META = [
        // data
        'data/domain-models' => ['title' => 'Domain-Level Models', 'summary' => 'Semitexa separates persistence resources from business models. Resources map tables; domain models carry behavior and invariants.'],
        'data/repository-workflow' => ['title' => 'Repository Workflow', 'summary' => 'The canonical Semitexa path: handlers depend on repository contracts, repositories return domain models, and persistence resources stay behind the boundary.'],
        'data/schema-sync' => ['title' => 'Schema Sync, Not Migration Churn', 'summary' => 'Semitexa creates SQL only when the real schema changed, blocks destructive drops by default, and logs the exact DDL plan as SQL and JSON.'],
        'data/query' => ['title' => 'Query Builder', 'summary' => 'Compose type-safe queries with a fluent API — no raw SQL, no magic strings.'],
        'data/filtering' => ['title' => 'Filtering', 'summary' => 'Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.'],
        'data/pagination' => ['title' => 'Pagination', 'summary' => 'Offset and cursor pagination out of the box — switch modes with a single query parameter.'],
        'data/relations' => ['title' => 'Relations', 'summary' => 'Declare parent and child links on the resource itself, then read typed relations from the handler.'],
        'data/table-extension' => ['title' => 'Shared Table Extension', 'summary' => 'Two modules can extend one table independently, and the ORM merges the schema without forcing either side to edit the other.'],
        'data/n-plus-one' => ['title' => 'N+1 Without Magic', 'summary' => 'Semitexa avoids N+1 by using resource slices for the exact columns and relations each screen needs, instead of hiding database traffic behind implicit relation loading.'],

        // auth
        'auth/session' => ['title' => 'Session Auth', 'summary' => 'Google signs the user in, then the session stores the selected demo role and re-hydrates it on every request.'],
        'auth/session-payloads' => ['title' => 'Session Payloads', 'summary' => 'Semitexa forbids string-key session chaos: session state lives in typed Session Payloads or it does not exist.'],
        'auth/google' => ['title' => 'Google Authorization', 'summary' => 'Authorization is required for demo SSE blocks that keep a long-lived backend connection open.'],
        'auth/machine' => ['title' => 'Machine Auth', 'summary' => 'Service-to-service authentication via Bearer tokens — scoped, revocable, and audited.'],
        'auth/protected' => ['title' => 'Protected Route', 'summary' => 'Add one attribute to any route and the framework enforces access — 403 returned automatically.'],
        'auth/requires-permission' => ['title' => 'Requires Permission', 'summary' => 'Declare one permission slug on the payload and let the framework enforce it before your handler runs.'],
        'auth/rbac' => ['title' => 'RBAC', 'summary' => 'Hybrid RBAC with coarse-grained capabilities, exact permission slugs, and module-owned permission catalogs.'],

        // events
        'events/arena' => ['title' => 'Execution Arena', 'summary' => 'Launch the same backend intent in sync, Swoole async, and queued modes, then watch the proof arrive over SSE.'],
        'events/sync' => ['title' => 'Sync Events', 'summary' => 'Dispatch an event and all sync listeners run before the response is sent.'],
        'events/deferred' => ['title' => 'Deferred Handler', 'summary' => 'Heavy work runs after the response is sent — the user gets instant feedback.'],
        'events/queued' => ['title' => 'Queued Handler', 'summary' => 'Events survive restarts and scale across workers — backed by a durable message queue.'],
        'events/sse' => ['title' => 'SSE Stream', 'summary' => 'Real-time server push without WebSockets — connect once and receive real backend events over plain HTTP.'],
        'events/ledger' => ['title' => 'Ledger Demo', 'summary' => 'Dispatch a protected demo event and inspect only the persisted demo ledger rows through a safe read-only view.'],

        // rendering
        'rendering/philosophy' => ['title' => 'SSR Philosophy', 'summary' => 'Semitexa SSR is one continuous rendering architecture: page, slots, deferred regions, live refresh, and interactive components stay inside one server-owned story.'],
        'rendering/resource-dtos' => ['title' => 'Resource DTOs', 'summary' => 'A Resource DTO is the one typed source of presentation data: handlers shape it once, templates consume it everywhere, and no view has to dissect random arrays.'],
        'rendering/slots' => ['title' => 'Slot Resources', 'summary' => 'Each page region is its own resource pipeline with the same template system as the main page — no scattered partial glue, no mystery wiring.'],
        'rendering/components' => ['title' => 'Components', 'summary' => 'Reusable, attribute-registered UI components — discovered automatically from the classmap.'],
        'rendering/seo' => ['title' => 'SEO', 'summary' => 'Set title, description, and Open Graph tags from your handler — no template hacks needed.'],
        'rendering/assets' => ['title' => 'Asset Pipeline', 'summary' => 'Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.'],
        'rendering/component-scripts' => ['title' => 'Component Script Assets', 'summary' => 'A Semitexa SSR component can own its optional enhancement asset, so behavior travels with the component instead of leaking into page-level glue.'],
        'rendering/deferred-scripts' => ['title' => 'Script Injection', 'summary' => 'Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.'],
        'rendering/deferred' => ['title' => 'Deferred Blocks', 'summary' => 'SSR renders the shell first, then expensive regions stream in as real HTML over SSE — no SPA handoff and no client-side page rebuild.', 'opensInNewTab' => true],
        'rendering/deferred-encapsulation' => ['title' => 'Block Isolation', 'summary' => 'Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.'],
        'rendering/deferred-live' => ['title' => 'Live Widgets', 'summary' => 'A live slot can refresh itself on a timer while the page stays SSR-first — no SPA runtime and no handwritten polling layer.'],
        'rendering/reactive-report' => ['title' => 'Reactive Report', 'summary' => 'Background work updates an SSR-first slot in place, so the UI feels live without falling back to SPA state orchestration.'],
        'rendering/reactive-import' => ['title' => 'Reactive Import', 'summary' => 'Background batches keep moving, and the page reflects server progress as live HTML instead of a client-managed progress app.'],
        'rendering/reactive-analytics' => ['title' => 'Reactive Analytics', 'summary' => 'Independent analytics jobs can light up one dashboard progressively, while the page stays server-rendered from the first byte.'],
        'rendering/reactive-ai' => ['title' => 'Reactive AI Task', 'summary' => 'Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.'],

        // platform
        'platform/tenancy-resolution' => ['title' => 'Tenant Context Resolution', 'summary' => 'See how Semitexa resolves the active tenant from subdomain, header, path, or query input before the rest of the platform runs.'],
        'platform/tenancy-config' => ['title' => 'Per-Tenant Configuration', 'summary' => 'Three demo tenants with distinct branding — switch tenant, everything changes without if/else.'],
        'platform/tenancy-layers' => ['title' => 'Multi-Layer Tenancy', 'summary' => 'Organization → Locale → Theme → Environment — four independent layers compose into one TenantContext.'],
        'platform/tenancy-isolation' => ['title' => 'Data Isolation', 'summary' => 'Product listing scoped by tenant — switch tenant, list changes. Zero manual WHERE clauses.'],
        'platform/tenancy-queue' => ['title' => 'Queue Tenant Propagation', 'summary' => 'Tenant context travels with queued jobs — _tenant key injected automatically, restored by worker.'],

        // api
        'api/rest-api' => ['title' => 'REST API', 'summary' => 'Classic Semitexa REST endpoints with typed payloads, versioning, and consumer-friendly response shaping.'],
        'api/structured-errors' => ['title' => 'Structured Errors', 'summary' => 'Throw domain exceptions and let semitexa-api map them into stable machine-readable error envelopes.'],
        'api/active-version' => ['title' => 'Active Version', 'summary' => 'The current collection endpoint with a clean X-Api-Version header and no deprecation noise.'],
        'api/sunset-version' => ['title' => 'Sunset Version', 'summary' => 'A deprecated product endpoint that emits both Deprecation and Sunset headers.'],
        'api/schema-discovery' => ['title' => 'Schema Discovery', 'summary' => 'A mini Swagger-style explorer for the live product API contract, schema endpoint, and response shapes.'],
        'api/graphql' => ['title' => 'GraphQL API', 'summary' => 'GraphQL-first Semitexa contracts built with typed payloads and typed output DTOs instead of resolver sprawl.'],
        'api/rest-graphql' => ['title' => 'REST + GraphQL', 'summary' => 'One Semitexa use case can serve both REST and GraphQL without duplicating handler logic into separate resolver classes.'],

        // cli
        'cli/describe-commands' => ['title' => 'Project Describe Commands', 'summary' => 'Routes, modules, contracts, and handlers can be described directly from the CLI instead of reverse-engineering the framework graph by hand.'],
        'cli/runtime-maintenance' => ['title' => 'Runtime Maintenance', 'summary' => 'Reload workers, clear stale cache, sync registries, lint architecture rules, and probe handler wiring without reaching for ad-hoc shell scripts.'],
        'cli/scaffolding-generators' => ['title' => 'Scaffolding Generators', 'summary' => 'Scaffold modules, pages, payloads, services, and contracts through commands that already understand Semitexa structure and AI-friendly output modes.'],
        'cli/workers-scheduling' => ['title' => 'Workers & Scheduling', 'summary' => 'Run queues, scheduler pools, mail delivery, webhooks, and tenant-scoped commands from a coherent operator surface instead of bespoke daemons.'],
        'cli/ai-tooling' => ['title' => 'AI Tooling Surface', 'summary' => 'Semitexa exposes AI-facing commands as explicit CLI contracts: capabilities, skills, log access, and a local assistant entrypoint.'],
        'cli/orm-console' => ['title' => 'ORM Console Toolkit', 'summary' => 'The ORM ships with a practical CLI surface: status, diff, sync, and seed commands with dry-run safety and SQL plan export.'],

        // llm
        'llm/overview' => ['title' => 'Overview', 'summary' => 'What `semitexa/llm` adds to the framework and how your project can expose its own CLI skills to the assistant.'],
        'llm/providers' => ['title' => 'Providers & Backends', 'summary' => 'Provider contracts, backend resolution, local vs remote Ollama, and the environment knobs that shape LLM runtime behavior.'],
        'llm/skills' => ['title' => 'Adding Skills', 'summary' => 'How a console command becomes AI-executable through `#[AsAiSkill]`, metadata policy, and registry discovery.'],
        'llm/execution-flow' => ['title' => 'Execution Flow', 'summary' => 'How a user request becomes a planner decision, a reviewed skill proposal, and finally a real console execution.'],

        // testing
        'testing/payload-contracts' => ['title' => 'Payload Contract Testing', 'summary' => 'Scaffold one project-level contract test and let strategy profiles verify payload boundaries without hand-writing repetitive negative cases.'],
    ];

    /**
     * @var array<string, array{title: string, summary: string}>|null
     */
    private ?array $docsFeatureMeta = null;

    public function getSections(bool $includeEmpty = false): array
    {
        $sections = [];

        foreach (self::SECTION_META as $key => $meta) {
            $flatFeatures = $this->buildFeaturesForSection($key);
            $featureCount = count($flatFeatures);

            if (!$includeEmpty && $featureCount === 0) {
                continue;
            }

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

    /**
     * @return list<array<string, mixed>>
     */
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
        $meta = $this->resolveFeatureMeta($section, $slug);

        if ($meta === null) {
            return null;
        }

        $href = '/demo/' . $section . '/' . $slug;
        if ($slug === 'parameterized') {
            $href .= '/headphones';
        }

        return [
            'section' => $section,
            'slug' => $slug,
            'label' => self::SECTION_META[$section]['label'] ?? ucfirst($section),
            'title' => $meta['title'],
            'summary' => $meta['summary'],
            'opensInNewTab' => $meta['opensInNewTab'] ?? false,
            'href' => $href,
        ];
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
     * Builds the flat feature list for a section from SECTION_GROUPS + FEATURE_META.
     *
     * @return list<array{section:string,slug:string,label:string,title:string,summary:string,opensInNewTab:bool,href:string}>
     */
    private function buildFeaturesForSection(string $section): array
    {
        $features = [];
        $groups = self::SECTION_GROUPS[$section] ?? [];

        foreach ($groups as $group) {
            foreach ($group['slugs'] as $slug) {
                $meta = $this->resolveFeatureMeta($section, $slug);

                if ($meta === null) {
                    continue;
                }

                $href = '/demo/' . $section . '/' . $slug;
                if ($slug === 'parameterized') {
                    $href .= '/headphones';
                }

                $features[] = [
                    'section' => $section,
                    'slug' => $slug,
                    'label' => self::SECTION_META[$section]['label'] ?? ucfirst($section),
                    'title' => $meta['title'],
                    'summary' => $meta['summary'],
                    'opensInNewTab' => $meta['opensInNewTab'] ?? false,
                    'href' => $href,
                ];
            }
        }

        return $features;
    }

    /**
     * @return array{title: string, summary: string, opensInNewTab?: true}|null
     */
    private function resolveFeatureMeta(string $section, string $slug): ?array
    {
        $key = $section . '/' . $slug;

        if (in_array($section, self::DOCS_BACKED_SECTIONS, true)) {
            return $this->loadDocsFeatureMeta()[$key] ?? null;
        }

        return self::FEATURE_META[$key] ?? null;
    }

    /**
     * @return array<string, array{title: string, summary: string}>
     */
    private function loadDocsFeatureMeta(): array
    {
        if ($this->docsFeatureMeta !== null) {
            return $this->docsFeatureMeta;
        }

        $meta = [];

        foreach ($this->documentManifestBuilder->buildBySection() as $section => $items) {
            if (!in_array($section, self::DOCS_BACKED_SECTIONS, true)) {
                continue;
            }

            foreach ($items as $item) {
                $meta[$item->id->toString()] = [
                    'title' => $item->metadata->title,
                    'summary' => $item->metadata->summary,
                ];
            }
        }

        $this->docsFeatureMeta = $meta;

        return $this->docsFeatureMeta;
    }

    /**
     * @param list<array{section:string,slug:string,label:string,title:string,summary:string,opensInNewTab:bool,href:string}> $features
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
