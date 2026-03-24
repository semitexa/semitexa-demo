<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

/**
 * Provides structured explanation data for each demo feature.
 *
 * Explanations are stored as PHP arrays, not Twig template text.
 * This keeps content manageable and enables future export
 * (docs generation, onboarding wizard, etc.).
 */
final class DemoExplanationProvider
{
    /**
     * @return array{what: string, how: string, why: string, keywords: list<array{term: string, definition: string}>}|null
     */
    public function getExplanation(string $section, string $slug): ?array
    {
        $key = $section . '/' . $slug;

        return self::EXPLANATIONS[$key] ?? null;
    }

    /**
     * @return list<array{term: string, definition: string}>
     */
    public function getKeywords(string $section, string $slug): array
    {
        $explanation = $this->getExplanation($section, $slug);

        return $explanation['keywords'] ?? [];
    }

    private const EXPLANATIONS = [
        // --- Routing section ---
        'routing/basic' => [
            'what' => 'A single #[AsPayload] attribute on a PHP class creates a fully routed HTTP endpoint — no XML, no YAML, no config files.',
            'how' => 'The framework scans the Composer classmap for classes with #[AsPayload], extracts path and method metadata, and registers routes at boot. The route compiler turns path patterns into optimized regex matchers cached in memory.',
            'why' => 'Keeping route definitions co-located with their request DTOs means a reader can understand what an endpoint accepts and where it lives by reading a single file. There is no "routes file" to keep in sync.',
            'keywords' => [
                ['term' => '#[AsPayload]', 'definition' => 'Attribute that marks a class as a request DTO and declares its HTTP route.'],
                ['term' => 'responseWith', 'definition' => 'Links the payload to the Resource DTO that shapes the response.'],
                ['term' => 'ClassDiscovery', 'definition' => 'Reads the Composer classmap to find all classes with a given attribute.'],
            ],
        ],
        'routing/parameterized' => [
            'what' => 'Path parameters like {slug} are extracted from the URL and injected into the payload DTO via setter methods.',
            'how' => 'The router uses regex requirements to constrain what each parameter matches. The RequestDtoHydrator calls setters on the payload DTO with the extracted values. Default values are used when a parameter is not present.',
            'why' => 'Typed path parameters with regex constraints prevent invalid data from reaching handler code. The handler can trust that $payload->slug is always a valid, matched value.',
            'keywords' => [
                ['term' => 'requirements', 'definition' => 'Regex patterns that constrain path parameter values at the routing level.'],
                ['term' => 'RequestDtoHydrator', 'definition' => 'Populates payload DTO properties by calling setter methods with request data.'],
            ],
        ],
        'routing/content-negotiation' => [
            'what' => 'A single endpoint serves JSON, HTML, or other formats depending on the Accept header or ?_format= query parameter.',
            'how' => 'The produces array in #[AsPayload] declares which Content-Types the endpoint supports. The framework negotiates the best match against the client Accept header and selects the appropriate response serializer.',
            'why' => 'Content negotiation lets one route serve both browser and API clients. The handler stays format-agnostic — it populates the resource DTO, and the framework handles serialization.',
            'keywords' => [
                ['term' => 'produces', 'definition' => 'Declares the Content-Types an endpoint can return, enabling content negotiation.'],
                ['term' => 'Accept header', 'definition' => 'HTTP header the client sends to indicate preferred response formats.'],
            ],
        ],
        'routing/typed-handler' => [
            'what' => 'Handlers declare concrete Payload and Resource types in their handle() signature — no instanceof, no casting, no guessing.',
            'how' => 'TypedHandlerInterface enforces that handle() accepts a specific Payload type and returns a specific Resource type. The HandlerReflectionCache validates signatures at boot, catching mismatches before any request is served.',
            'why' => 'Concrete types in the handler signature eliminate an entire class of runtime errors. IDEs provide full autocompletion, and static analysis tools can verify correctness without running the code.',
            'keywords' => [
                ['term' => 'TypedHandlerInterface', 'definition' => 'Interface requiring concrete Payload and Resource types in the handle() method signature.'],
                ['term' => 'HandlerReflectionCache', 'definition' => 'Validates handler method signatures at boot to catch type mismatches early.'],
                ['term' => '#[AsPayloadHandler]', 'definition' => 'Attribute that registers a class as the handler for a specific (Payload, Resource) pair.'],
            ],
        ],

        // --- Container / DI section ---
        'di/basic-injection' => [
            'what' => 'Services are injected into handlers via #[InjectAsReadonly] property attributes — no constructor wiring, no YAML.',
            'how' => 'The DI container reads injection attributes on handler properties and populates them after construction. #[InjectAsReadonly] services are built once per worker and shared across requests.',
            'why' => 'Property injection with explicit lifecycle attributes makes dependency scope visible in the code. A reader can tell at a glance whether a dependency is shared or per-request.',
            'keywords' => [
                ['term' => '#[InjectAsReadonly]', 'definition' => 'Injects a service that is built once per worker and shared across all requests.'],
                ['term' => '#[InjectAsMutable]', 'definition' => 'Injects a service that is cloned per request, safe for request-scoped state.'],
            ],
        ],
        'di/service-contracts' => [
            'what' => 'Service contracts declare what a module provides. #[SatisfiesServiceContract] marks the concrete implementation.',
            'how' => 'When multiple modules provide the same contract, the module hierarchy (extends) determines which implementation wins. The registry generates resolver classes that make substitution explicit.',
            'why' => 'Explicit contracts prevent hidden substitution. A reader can find every implementation of an interface without searching the whole codebase.',
            'keywords' => [
                ['term' => '#[SatisfiesServiceContract]', 'definition' => 'Marks a class as the implementation of a service contract interface.'],
                ['term' => 'ContractFactoryInterface', 'definition' => 'Factory for multi-implementation contracts — resolves by key at runtime.'],
            ],
        ],
        'di/scoped-services' => [
            'what' => 'Mutable services are cloned per request, preventing state leakage between concurrent requests in a Swoole worker.',
            'how' => 'The container maintains a readonly tier (shared, built once) and a mutable tier (prototype, cloned per get()). #[InjectAsMutable] services receive the current request context after cloning.',
            'why' => 'In a long-running Swoole process, shared mutable state is a correctness bug. The two-tier model makes lifecycle explicit — if a service holds request state, it must be mutable.',
            'keywords' => [
                ['term' => 'Two-tier DI', 'definition' => 'Readonly (worker-shared) + mutable (request-cloned) container tiers for Swoole safety.'],
            ],
        ],

        // --- Data / ORM section ---
        'data/orm-models' => [
            'what' => 'Domain models are plain PHP classes with ORM attributes — #[FromTable], #[PrimaryKey], #[Column] — no base class required.',
            'how' => 'The ORM reads attributes at boot to build table metadata. HasUuidV7 generates time-ordered UUIDs. HasTimestamps manages created_at/updated_at. Repositories provide typed query methods.',
            'why' => 'Attribute-driven models keep the domain layer free of framework base classes. The model is a plain PHP object that happens to have persistence metadata.',
            'keywords' => [
                ['term' => '#[FromTable]', 'definition' => 'Maps a model class to a database table.'],
                ['term' => 'HasUuidV7', 'definition' => 'Trait providing time-ordered UUID v7 primary keys.'],
                ['term' => '#[TenantScoped]', 'definition' => 'Automatically filters queries by the current tenant ID.'],
            ],
        ],
        'data/repositories' => [
            'what' => 'Repositories provide typed query methods and implement domain interfaces. The ORM repository is an implementation detail.',
            'how' => 'AbstractRepository provides a fluent query builder — select(), where(), orderBy(), fetchAll(). Domain code depends on the repository interface, not the ORM implementation.',
            'why' => 'Typed repository interfaces make data access explicit and testable. The handler asks for what it needs; the repository decides how to get it.',
            'keywords' => [
                ['term' => '#[SatisfiesRepositoryContract]', 'definition' => 'Marks a class as the ORM implementation of a domain repository interface.'],
                ['term' => 'AbstractRepository', 'definition' => 'Base class providing fluent query builder methods for ORM repositories.'],
            ],
        ],
        'data/seeder' => [
            'what' => 'The demo data seeder creates a realistic dataset on first boot — products, categories, orders, reviews — with tenant isolation.',
            'how' => 'DemoDataSeeder checks for existing data and seeds if empty. Each entity gets a deterministic UUID so re-seeding is idempotent. Tenant-scoped models are seeded per tenant.',
            'why' => 'Realistic seed data makes the demo immediately useful. Deterministic IDs mean URLs in documentation stay stable across re-seeds.',
            'keywords' => [
                ['term' => 'DemoDataSeeder', 'definition' => 'Service that creates demo data on first boot, idempotent via deterministic UUIDs.'],
            ],
        ],

        // --- Auth section ---
        'auth/session-auth' => [
            'what' => 'Session-based authentication with cookie binding. Login sets a session, subsequent requests restore the authenticated user.',
            'how' => 'The auth pipeline listener reads the session cookie, loads the user from the session store, and populates AuthContext. Handlers check $this->auth->isGuest() or access the authenticated user.',
            'why' => 'Session auth is the foundation for browser-based demos. The pipeline listener pattern keeps auth logic out of individual handlers.',
            'keywords' => [
                ['term' => 'AuthContext', 'definition' => 'Request-scoped service holding the authenticated user (or guest state).'],
                ['term' => '#[PublicEndpoint]', 'definition' => 'Marks a payload as accessible without authentication.'],
            ],
        ],
        'auth/rbac' => [
            'what' => 'Role-Based Access Control: users have roles, roles have permissions. #[RequiresPermission] enforces access at the route level.',
            'how' => 'The authorization pipeline listener reads #[RequiresPermission] from the payload, checks the authenticated user\'s permissions via RbacService, and throws AccessDeniedException on failure.',
            'why' => 'Declarative permissions on payloads make access control visible and auditable. A security review can scan payload attributes to understand the access model.',
            'keywords' => [
                ['term' => '#[RequiresPermission]', 'definition' => 'Requires the authenticated user to hold a specific permission slug.'],
                ['term' => 'RbacService', 'definition' => 'Service that resolves user → roles → permissions for access control checks.'],
            ],
        ],

        // --- Events section ---
        'events/sync-dispatch' => [
            'what' => 'Events decouple side effects from handlers. Dispatch an event and listeners react — notifications, cache invalidation, analytics.',
            'how' => 'EventDispatcher::create() builds the event, dispatch() triggers all registered listeners. #[AsEventListener] marks listener classes. Default execution is synchronous in the same coroutine.',
            'why' => 'Events let handlers focus on the main task. Side effects belong in listeners, keeping handler code clean and each concern independently testable.',
            'keywords' => [
                ['term' => '#[AsEventListener]', 'definition' => 'Registers a class as a listener for a specific event type.'],
                ['term' => 'EventDispatcher', 'definition' => 'Core service for creating and dispatching domain events.'],
            ],
        ],
        'events/async-defer' => [
            'what' => 'Async events run after the response is sent — via Swoole::defer() or a queue transport.',
            'how' => 'Set execution mode to Async or Queued on the listener attribute. Async defers to the event loop. Queued publishes to the configured queue transport for background processing.',
            'why' => 'Heavy side effects (email, external API calls) should not block the response. Async/queued execution keeps response times fast while ensuring side effects complete.',
            'keywords' => [
                ['term' => 'EventExecution::Async', 'definition' => 'Defers listener execution via Swoole::defer() — runs after response.'],
                ['term' => 'EventExecution::Queued', 'definition' => 'Publishes the event to a queue transport for background processing.'],
            ],
        ],

        // --- Rendering / SSR section ---
        'rendering/twig-templates' => [
            'what' => 'Pages are rendered with Twig templates. The resource DTO declares its template via #[AsResource], and the framework renders it automatically.',
            'how' => 'After all handlers complete, toCoreResponse() calls renderTemplate() with the declared template. Template variables come from the resource\'s with*() methods. Layouts use {% extends %} Twig inheritance.',
            'why' => 'Declarative templates on resources eliminate render calls from handlers. The handler populates data; the framework renders it. No forgotten render calls, no mismatched templates.',
            'keywords' => [
                ['term' => '#[AsResource]', 'definition' => 'Declares the render handle and template for a resource DTO.'],
                ['term' => 'renderTemplate()', 'definition' => 'Renders a Twig template with the resource\'s accumulated context.'],
            ],
        ],
        'rendering/slot-resources' => [
            'what' => 'Slot resources are independent page regions — sidebar, footer, widgets — hydrated by their own handlers, not the page handler.',
            'how' => '#[AsSlotResource] registers a slot for a specific page handle. The slot system instantiates the slot DTO, runs its slot handler pipeline, and renders the slot template. Slots can be deferred (loaded after page load via SSE).',
            'why' => 'Slots keep page handlers focused on page-level data. Independent blocks get their own handlers, making them reusable across pages and independently cacheable.',
            'keywords' => [
                ['term' => '#[AsSlotResource]', 'definition' => 'Registers a class as a renderable slot for a specific page handle.'],
                ['term' => 'layout_slot()', 'definition' => 'Twig function that renders a named slot in a layout template.'],
            ],
        ],

        // --- Async section ---
        'async/deferred-blocks' => [
            'what' => 'Deferred blocks load after the initial page render — the page shows a skeleton, then SSE pushes the real content.',
            'how' => '#[AsSlotResource(deferred: true)] marks a slot for deferred loading. The page renders with skeletonTemplate first. A client-side JS module connects to the SSE endpoint, which renders and streams the slot HTML.',
            'why' => 'Deferred blocks improve perceived performance. The page shell loads instantly; expensive data (charts, feeds, recommendations) streams in without blocking.',
            'keywords' => [
                ['term' => 'deferred: true', 'definition' => 'Slot attribute flag that delays rendering until after the initial page load.'],
                ['term' => 'skeletonTemplate', 'definition' => 'Placeholder template shown while a deferred slot loads.'],
                ['term' => 'SSE', 'definition' => 'Server-Sent Events — one-way server-to-client streaming over HTTP.'],
            ],
        ],
        'async/reactive-updates' => [
            'what' => 'Reactive blocks auto-refresh on a timer — live dashboards, activity feeds, real-time metrics.',
            'how' => 'refreshInterval on #[AsSlotResource] sets the polling interval in seconds. The client module reconnects to the SSE endpoint on each interval, receiving fresh HTML.',
            'why' => 'Reactive updates provide a live feel without WebSockets. The slot system handles reconnection, error recovery, and DOM patching automatically.',
            'keywords' => [
                ['term' => 'refreshInterval', 'definition' => 'Seconds between automatic slot re-renders for live data.'],
            ],
        ],

        // --- Platform section ---
        'platform/multi-tenancy' => [
            'what' => 'Multi-tenancy isolates data per tenant. #[TenantScoped] on a model automatically filters all queries by the current tenant.',
            'how' => 'TenantContext holds the current tenant ID (resolved from subdomain, header, or session). The ORM query builder injects WHERE tenant_id = ? on all TenantScoped models. Tenant switching is a context operation, not a connection switch.',
            'why' => 'Automatic tenant scoping eliminates the most dangerous multi-tenancy bug: accidentally querying another tenant\'s data. The developer cannot forget the filter.',
            'keywords' => [
                ['term' => '#[TenantScoped]', 'definition' => 'ORM attribute that auto-filters queries by the current tenant ID.'],
                ['term' => 'TenantContext', 'definition' => 'Request-scoped store holding the current tenant identity.'],
            ],
        ],
        'platform/i18n' => [
            'what' => 'Locale-aware content via trans() in Twig and locale files per tenant. Each tenant can override any translation key.',
            'how' => 'Locale files live in Application/View/locales/tenants/{tenant}/. The trans() function resolves keys through a tenant → module → fallback chain. Locale is set from the request Accept-Language header or session preference.',
            'why' => 'Per-tenant translation overrides let the same application speak different business languages. "Order" can be "Booking" for one tenant and "Request" for another.',
            'keywords' => [
                ['term' => 'trans()', 'definition' => 'Twig function that resolves a translation key through the locale chain.'],
            ],
        ],

        // --- Caching section ---
        'caching/slot-caching' => [
            'what' => 'Slot resources can be cached by setting cacheTtl on #[AsSlotResource]. Cached slots skip handler execution entirely.',
            'how' => 'The slot system checks the cache before invoking the slot handler pipeline. If a cached version exists and the TTL has not expired, the cached HTML is served directly. Cache keys include the page handle, slot ID, and tenant.',
            'why' => 'Slot caching provides fine-grained performance control. Expensive sidebar widgets can be cached for minutes while the main content stays fresh.',
            'keywords' => [
                ['term' => 'cacheTtl', 'definition' => 'Seconds to cache a slot\'s rendered HTML. Zero disables caching.'],
            ],
        ],

        // --- Search section ---
        'search/basic-search' => [
            'what' => 'Full-text search across demo data powered by the semitexa-search package — query parsing, filters, ranking.',
            'how' => 'SearchManager dispatches queries to configured backends. The ORM backend translates search criteria to SQL LIKE/= clauses. OrmRankingStrategy scores results by match type (exact > prefix > contains) weighted by field importance.',
            'why' => 'Search demonstrates the full pipeline: query → parse → plan → execute → rank → present. Each layer is independently configurable and replaceable.',
            'keywords' => [
                ['term' => 'SearchManager', 'definition' => 'Entry point for search queries — dispatches to configured backends.'],
                ['term' => 'OrmRankingStrategy', 'definition' => 'Scores search results by match quality and field weight.'],
            ],
        ],

        // --- API section ---
        'api/external-endpoints' => [
            'what' => '#[ExternalApi] marks endpoints as machine-facing. They get JSON error envelopes, version headers, and OpenAPI documentation.',
            'how' => 'ExternalApiExceptionMapper converts domain exceptions to structured JSON error responses. ApiVersion adds deprecation and sunset headers. The route inspection registry enumerates all external API routes for schema generation.',
            'why' => 'Separating external API behavior from internal endpoints keeps the internal developer experience simple while providing the polish that API consumers expect.',
            'keywords' => [
                ['term' => '#[ExternalApi]', 'definition' => 'Marks a payload as an external API endpoint with machine-facing error handling.'],
                ['term' => '#[ApiVersion]', 'definition' => 'Declares version, deprecation, and sunset metadata for an API endpoint.'],
                ['term' => 'MachineCredential', 'definition' => 'API key entity with scopes, revocation, and audit trail.'],
            ],
        ],
        'api/machine-auth' => [
            'what' => 'Machine-to-machine authentication via Bearer tokens. API clients authenticate with {id}:{secret} credentials.',
            'how' => 'MachineAuthHandler reads the Authorization: Bearer header, splits {id}:{secret}, validates against MachineCredentialRepository, and sets a MachinePrincipal on AuthContext.',
            'why' => 'Machine auth is stateless — no sessions, no cookies. Each request carries its own credentials, making it suitable for server-to-server communication.',
            'keywords' => [
                ['term' => 'MachineAuthHandler', 'definition' => 'Auth handler that validates Bearer {id}:{secret} tokens for API access.'],
                ['term' => 'MachinePrincipal', 'definition' => 'AuthenticatableInterface implementation wrapping a machine credential.'],
            ],
        ],

        // --- Testing section ---
        'testing/payload-contracts' => [
            'what' => 'Automated contract testing for payloads — #[TestablePayload] marks a payload for strategy-based validation.',
            'how' => 'The testing framework discovers testable payloads, applies strategy profiles (Standard, Strict, Paranoid), and runs security, type enforcement, and monkey testing strategies against each endpoint.',
            'why' => 'Contract tests verify that payloads reject bad input and accept good input without writing individual test cases. Strategy profiles let teams choose their risk tolerance.',
            'keywords' => [
                ['term' => '#[TestablePayload]', 'definition' => 'Marks a payload for automated contract testing with configurable strategies.'],
                ['term' => 'MonkeyTestingStrategy', 'definition' => 'Sends random/malformed input to test endpoint robustness.'],
            ],
        ],
    ];
}
