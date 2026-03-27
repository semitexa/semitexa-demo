<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attributes\AsService;

/**
 * Provides structured explanation data for each demo feature.
 *
 * Explanations are stored as PHP arrays, not Twig template text.
 * This keeps content manageable and enables future export
 * (docs generation, onboarding wizard, etc.).
 */
#[AsService]
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
        'routing/payload-shield' => [
            'what' => 'Payloads are the shield from external data: hydration, type casting, and validation happen before the handler, so application code works with one trusted object.',
            'how' => 'RequestDtoHydrator maps request input into the payload via typed setters, PayloadValidator calls validate() when the payload implements ValidatablePayload, and invalid input returns 422 before the handler is executed.',
            'why' => 'This makes single responsibility obvious. The payload owns the transport boundary and input truth. The handler owns the use case. Validation stops being scattered controller glue and becomes one explicit contract.',
            'keywords' => [
                ['term' => 'ValidatablePayload', 'definition' => 'Payload contract that lets the framework validate incoming data before the handler runs.'],
                ['term' => 'RequestDtoHydrator', 'definition' => 'Hydrates payload DTOs from HTTP input by calling typed setters.'],
                ['term' => 'PayloadValidator', 'definition' => 'Runs payload validation and short-circuits invalid requests with a consistent error response.'],
            ],
        ],
        'routing/public-endpoint' => [
            'what' => 'Semitexa is closed by default: every payload requires authentication unless you explicitly opt it into anonymous access with #[PublicEndpoint].',
            'how' => 'The access policy resolver inspects payload attributes at boot. If #[PublicEndpoint] is present, the route is marked public; otherwise the authorizer treats guest access as AuthenticationRequired and the pipeline returns 401 before the handler runs.',
            'why' => 'This flips the usual risk profile. Teams do not have to remember to secure every endpoint one by one. The safe default is built in, and public exposure becomes a deliberate code review event.',
            'keywords' => [
                ['term' => '#[PublicEndpoint]', 'definition' => 'Marks a payload as explicitly reachable without authentication.'],
                ['term' => 'default private', 'definition' => 'The absence of #[PublicEndpoint] means the endpoint is treated as protected for guests.'],
                ['term' => '401 Unauthorized', 'definition' => 'The framework response returned when a guest hits a protected endpoint.'],
                ['term' => 'Authorizer', 'definition' => 'Core service that decides whether the current subject may access the resolved payload.'],
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
        'data/table-extension' => [
            'what' => 'Two modules can point at the same table and contribute their own columns without one module editing the other module\'s resource class.',
            'how' => 'SchemaCollector groups discovered ORM resources by table name. When another resource maps to the same table, it adds only missing columns and leaves already-defined columns alone.',
            'why' => 'This removes one of the most painful ownership traps in modular systems: the first module does not permanently own the whole table forever. Later modules stay additive instead of invasive.',
            'keywords' => [
                ['term' => 'Shared table extension', 'definition' => 'Multiple ORM resources map to one physical table and extend its schema collaboratively.'],
                ['term' => 'SchemaCollector', 'definition' => 'Collects table definitions from resource attributes and merges columns by table name.'],
                ['term' => 'Module isolation', 'definition' => 'A later module can add persistence fields without reopening the source code of the original module.'],
            ],
        ],
        'data/domain-models' => [
            'what' => 'Semitexa deliberately separates persistence resources from domain-level business models instead of pretending one class should do both jobs.',
            'how' => 'A resource class maps the table and may implement DomainMappable. Repositories then return domain objects on the normal read path, while explicit resource reads stay available through fetchOneAsResource() and fetchAllAsResource().',
            'why' => 'This keeps business code free from ORM metadata and keeps persistence code free from fake business semantics. The boundary stays reviewable and intentional.',
            'keywords' => [
                ['term' => 'DomainMappable', 'definition' => 'Resource contract that converts storage resources to domain objects and back again.'],
                ['term' => 'Resource model', 'definition' => 'ORM-facing class responsible for table mapping, columns, indexes, and storage conversion.'],
                ['term' => 'Domain model', 'definition' => 'Business object carrying behavior, invariants, and ubiquitous language without persistence metadata.'],
            ],
        ],
        'data/repository-workflow' => [
            'what' => 'The canonical Semitexa repository workflow is domain-first: application code depends on repository contracts and works with domain models, not raw ORM resources.',
            'how' => 'Repository contracts return domain objects. ORM repository implementations sit behind the contract, convert through DomainMappable, and only expose resource-level reads through explicit methods like fetchOneAsResource().',
            'why' => 'This keeps the demo honest about best practices. Resource-level CRUD still exists, but it is not what we should teach as the primary architectural path.',
            'keywords' => [
                ['term' => 'Repository contract', 'definition' => 'Application-facing interface that expresses persistence in domain language.'],
                ['term' => 'Domain-first read path', 'definition' => 'Default repository reads return business models instead of persistence resources.'],
                ['term' => 'fetchOneAsResource()', 'definition' => 'Explicit low-level read path for infrastructure cases that truly need raw ORM resources.'],
            ],
        ],
        'data/schema-sync' => [
            'what' => 'Semitexa minimizes migration churn by computing schema changes directly from code and the current database instead of requiring constant hand-written migrations.',
            'how' => 'orm:sync collects the code schema, compares it with the live database, builds an execution plan, and separates safe changes from destructive ones. Drops use a two-phase flow: first mark deprecated, later drop only with explicit approval.',
            'why' => 'This reduces busywork, makes destructive intent visible, and still gives teams exact SQL artifacts when they need them for review or deployment.',
            'keywords' => [
                ['term' => 'orm:sync', 'definition' => 'Command that computes and optionally executes the schema synchronization plan.'],
                ['term' => 'Two-phase drop', 'definition' => 'Column or table removal is delayed: first deprecate, then drop on a later explicit destructive pass.'],
                ['term' => 'AuditLogger', 'definition' => 'Writes executed sync operations as both JSON and SQL files under var/migrations/history.'],
            ],
        ],
        'data/n-plus-one' => [
            'what' => 'Semitexa avoids N+1 by letting each screen define the exact table slice it needs, then batch-loading explicit relations for the whole result set.',
            'how' => 'You can model multiple resource abstractions over the same table. A slim list resource selects only the required columns, and StreamingHydrator loads relations for all hydrated resources in one batch instead of per-row lazy lookups.',
            'why' => 'This removes the usual ORM trade-off between over-fetching giant entities and hiding extra queries behind lazy loading. The fetch plan is explicit, reviewable, and stable under load.',
            'keywords' => [
                ['term' => 'N+1', 'definition' => 'A query anti-pattern where one base query triggers one extra query per returned row.'],
                ['term' => 'StreamingHydrator', 'definition' => 'Hydrates many rows and batch-loads relations once for the whole result set.'],
                ['term' => 'Resource slice', 'definition' => 'A narrow resource abstraction over a table containing only the columns and relations required by one use case.'],
            ],
        ],
        'data/repositories' => [
            'what' => 'Repositories provide typed query methods behind domain-facing contracts. The ORM repository is the storage implementation, not the business abstraction.',
            'how' => 'AbstractRepository can return domain models by default when the resource implements DomainMappable. When infrastructure code truly needs raw persistence objects, SelectQuery exposes fetchOneAsResource() and fetchAllAsResource() explicitly.',
            'why' => 'This keeps handlers and services speaking domain language most of the time, while still allowing resource-level operations when persistence details actually matter.',
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
        'auth/requires-permission' => [
            'what' => 'A payload can declare one required permission slug, and the framework enforces it before the handler is even called.',
            'how' => 'The authorization listener reads #[RequiresPermission] from the resolved payload, asks the authorizer for an access decision, and maps the result to 401 for guests or 403 for authenticated users missing the permission.',
            'why' => 'This keeps access control reviewable and removes defensive authorization code from handlers. The contract lives on the route boundary where reviewers expect to find it.',
            'keywords' => [
                ['term' => '#[RequiresPermission]', 'definition' => 'Declares the exact permission slug required to execute the payload.'],
                ['term' => '401 Unauthorized', 'definition' => 'Returned when a guest subject hits a permission-protected route.'],
                ['term' => '403 Forbidden', 'definition' => 'Returned when the subject is authenticated but missing the declared permission.'],
                ['term' => 'guard chain', 'definition' => 'The authorization flow that evaluates the payload policy before the handler runs.'],
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
        'rendering/resource-dtos' => [
            'what' => 'Resource DTOs are the real presentation boundary: handlers build one typed response object, and templates consume that object instead of reshaping ad hoc arrays.',
            'how' => 'A payload declares responseWith, the handler receives a concrete resource DTO, and the resource accumulates named presentation fields through with*() methods before HtmlResponse auto-renders the declared template.',
            'why' => 'This is real separation of data and presentation. The template stops doing data surgery, partials stop inventing their own mapping rules, and the whole view layer reads from one explicit source of truth.',
            'keywords' => [
                ['term' => '#[AsResource]', 'definition' => 'Declares the template and render handle for a typed response DTO.'],
                ['term' => 'HtmlResponse', 'definition' => 'Base SSR response that stores render context and auto-renders the declared template.'],
                ['term' => 'with*() methods', 'definition' => 'Explicit resource methods that define the vocabulary of data allowed to reach the template.'],
            ],
        ],
        'rendering/slot-resources' => [
            'what' => 'Slot resources make page regions first-class: nav, sidebar, info rails, widgets, and deferred panels all use the same resource-and-template model as the main page.',
            'how' => '#[AsSlotResource] registers a region for a layout handle. layout_slot() resolves the slot, SlotRenderer creates the resource, SlotHandlerPipeline hydrates it, and Twig renders it with the same conventions as any normal page response.',
            'why' => 'This avoids fragment chaos. Frontend and backend stop using different composition systems, and every page region becomes explicit, typed, reusable, and independently evolvable.',
            'keywords' => [
                ['term' => '#[AsSlotResource]', 'definition' => 'Registers a class as a renderable slot for a specific page handle.'],
                ['term' => 'layout_slot()', 'definition' => 'Twig function that renders a named slot in a layout template.'],
                ['term' => 'SlotHandlerPipeline', 'definition' => 'Executes typed handlers for a slot resource before rendering its template.'],
            ],
        ],
        'rendering/deferred' => [
            'what' => 'Deferred slots let the first response stay server-rendered and usable while slower regions arrive later as server-rendered HTML.',
            'how' => '#[AsSlotResource(deferred: true)] marks a region for late delivery. The page sends skeleton HTML first, then DeferredBlockOrchestrator streams final slot HTML over SSE and the client swaps it in place.',
            'why' => 'This is SSR-first live UI, not a client-side page rebuild. The shell stays fast, and heavy regions remain inside the same template and response model as the rest of the page.',
            'keywords' => [
                ['term' => 'deferred: true', 'definition' => 'Moves a slot out of the critical render path without removing it from SSR.'],
                ['term' => 'skeletonTemplate', 'definition' => 'Placeholder HTML rendered immediately while the real slot is still preparing.'],
                ['term' => 'DeferredBlockOrchestrator', 'definition' => 'Coordinates server-side delivery of deferred slot HTML over SSE.'],
            ],
        ],
        'rendering/deferred-live' => [
            'what' => 'A live slot can keep refreshing itself while the page remains SSR-first and HTML-driven.',
            'how' => 'refreshInterval on #[AsSlotResource] tells the framework to re-fetch the slot over SSE on a timer. The client swaps HTML in place and reconnects automatically if the stream drops.',
            'why' => 'This gives you live widgets without building a parallel client-side rendering system just to maintain one dynamic region.',
            'keywords' => [
                ['term' => 'refreshInterval', 'definition' => 'Declarative live-refresh interval for a slot resource.'],
                ['term' => 'SSE reconnection', 'definition' => 'Keeps the live slot updating even if the stream temporarily drops.'],
                ['term' => 'SSR-first live UI', 'definition' => 'A live region remains part of the server-rendered page model instead of becoming a mini SPA.'],
            ],
        ],
        'rendering/reactive-report' => [
            'what' => 'Reactive slots reflect changing server state as live HTML, so scheduled jobs and background work appear in the UI without page reloads.',
            'how' => 'A job updates storage, ReactiveReportSlot re-renders from DemoJobRun state on each refresh tick, and the page swaps the returned HTML into the existing shell.',
            'why' => 'The page feels live, but the architecture remains coherent: one SSR-first shell, one slot model, one rendering pipeline, no separate frontend state machine.',
            'keywords' => [
                ['term' => 'ReactiveReportSlot', 'definition' => 'Deferred slot resource that turns background job state into live HTML.'],
                ['term' => 'DemoJobRun', 'definition' => 'Stores report execution state consumed by the live slot.'],
                ['term' => 'SSR-first live UI', 'definition' => 'Background changes appear in the page through server-rendered slot updates, not SPA state orchestration.'],
            ],
        ],
        'rendering/reactive-import' => [
            'what' => 'Long-running import progress stays owned by the server, and the page simply keeps receiving fresh HTML snapshots of that truth.',
            'how' => 'The import job writes progress_percent and progress_message into DemoJobRun. ReactiveImportSlot re-renders on refreshInterval and the browser swaps the returned HTML into the existing page shell.',
            'why' => 'You do not need a frontend progress engine just to keep a counter moving. The server stays authoritative, and the UI still feels live.',
            'keywords' => [
                ['term' => 'DemoJobRun', 'definition' => 'Stores the import state that the live slot turns into visible progress.'],
                ['term' => 'ReactiveImportSlot', 'definition' => 'Deferred slot resource that keeps re-rendering the import panel as batches advance.'],
                ['term' => 'SSR-first live UI', 'definition' => 'The page remains server-rendered even while progress updates keep arriving.'],
            ],
        ],
        'rendering/reactive-analytics' => [
            'what' => 'A live dashboard can be assembled from independent server snapshots, so panels update progressively without waiting for one giant frontend state sync.',
            'how' => 'Analytics jobs write their own snapshots, ReactiveAnalyticsSlot re-renders on refreshInterval, and each panel reflects the latest server state as soon as it is available.',
            'why' => 'This keeps dashboards honest and incremental. The UI feels live, but the architecture stays SSR-first instead of drifting into client-side orchestration code.',
            'keywords' => [
                ['term' => 'DemoAnalyticsSnapshot', 'definition' => 'Stores one analytics slice so panels can update independently.'],
                ['term' => 'ReactiveAnalyticsSlot', 'definition' => 'Deferred slot that turns the latest snapshot set into a live dashboard panel surface.'],
                ['term' => 'SSR-first live UI', 'definition' => 'Panels update as server-rendered HTML instead of a client app rebuilding the dashboard.'],
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
        'api/schema-discovery' => [
            'what' => 'Schema Discovery turns the raw `_schema` endpoint into a small interactive API console. The page still talks to the real live Semitexa routes.',
            'how' => 'The human-facing demo page is a normal DemoFeatureResource, but each operation button issues a fetch() call against the external API endpoints under `/demo/api/...`. The schema contract and example responses are preloaded server-side so the page reads like documentation before you click anything.',
            'why' => 'This is closer to how people evaluate APIs in practice: they want to poke the contract, compare responses, and confirm the system shape without wiring Postman first.',
            'keywords' => [
                ['term' => '_schema', 'definition' => 'Machine-facing schema endpoint returning JSON Schema for the product contract.'],
                ['term' => 'application/schema+json', 'definition' => 'Explicit media type for tooling that consumes JSON Schema documents.'],
                ['term' => 'Sparse fieldset', 'definition' => 'A client asks for only the fields it needs via `fields=...`.'],
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
        'testing/orm-console' => [
            'what' => 'The ORM includes a practical console toolkit for schema inspection, diffing, syncing, and seeding with safe defaults.',
            'how' => 'orm:status reports server capabilities and sync state, orm:diff shows the delta, orm:sync can dry-run or export the SQL plan, and orm:seed applies defaults() upserts for seedable resources.',
            'why' => 'A framework should not stop at attributes and repositories. Real teams need an operational surface that explains what will happen before it changes production state.',
            'keywords' => [
                ['term' => 'orm:status', 'definition' => 'Reports database/server capabilities and whether the schema is currently in sync.'],
                ['term' => 'orm:diff', 'definition' => 'Shows structural differences between the code schema and the live database.'],
                ['term' => '--output', 'definition' => 'Exports the computed SQL plan to a file for audit, review, or deployment pipelines.'],
            ],
        ],
    ];
}
