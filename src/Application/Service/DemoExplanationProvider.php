<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

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
            'how' => 'The framework scans the Composer classmap for classes with #[AsPayload], extracts path and method metadata, resolves env:: placeholders if present, and registers routes at boot. The route compiler then turns path patterns into optimized regex matchers cached in memory.',
            'why' => 'Keeping route definitions co-located with their request DTOs means a reader can understand what an endpoint accepts and where it lives by reading a single file. At the same time, env:: syntax lets operations move a route without reopening PHP code when deployment topology demands it.',
            'keywords' => [
                ['term' => '#[AsPayload]', 'definition' => 'Attribute that marks a class as a request DTO and declares its HTTP route.'],
                ['term' => 'env::VAR_NAME::/default/path', 'definition' => 'Special attribute syntax that lets a payload read its route path, name, or other metadata from .env with a safe default fallback.'],
                ['term' => 'responseWith', 'definition' => 'Links the payload to the Resource DTO that shapes the response.'],
                ['term' => 'ClassDiscovery', 'definition' => 'Reads the Composer classmap to find all classes with a given attribute.'],
            ],
        ],
        'routing/env-route-override' => [
            'what' => 'A payload can keep the route contract in PHP while still letting operations move the public URL through .env.',
            'how' => 'AsPayload path values support env::VAR::/fallback syntax. During route discovery, Semitexa resolves the env key first and falls back to the inline path when the variable is absent.',
            'why' => 'This gives deployment flexibility without losing the architectural advantage of payload-owned routes. The route remains reviewable in code, but environment-specific URL decisions stop forcing PHP edits.',
            'keywords' => [
                ['term' => 'env::VAR_NAME::/fallback', 'definition' => 'Environment-aware attribute syntax that resolves to an env value with a safe inline default.'],
                ['term' => 'resolved route metadata', 'definition' => 'The runtime route definition after env placeholders, inherited attributes, and response metadata have been normalized.'],
                ['term' => 'payload-owned route contract', 'definition' => 'The payload DTO remains the canonical place where path, methods, response type, and alternates are declared.'],
            ],
        ],
        'routing/payload-shield' => [
            'what' => 'Payloads are the shield from external data: hydration, type casting, and validation happen before the handler, so application code works with one trusted object.',
            'how' => 'PayloadHydrator maps request input into the payload via typed setters, PayloadValidator calls validate() when the payload implements ValidatablePayload, and invalid input returns 422 before the handler is executed.',
            'why' => 'This makes single responsibility obvious. The payload owns the transport boundary and input truth. The handler owns the use case. Validation stops being scattered controller glue and becomes one explicit contract.',
            'keywords' => [
                ['term' => 'ValidatablePayload', 'definition' => 'Payload contract that lets the framework validate incoming data before the handler runs.'],
                ['term' => 'PayloadHydrator', 'definition' => 'Hydrates payload DTOs from HTTP input by calling typed setters.'],
                ['term' => 'PayloadValidator', 'definition' => 'Runs payload validation and short-circuits invalid requests with a consistent error response.'],
            ],
        ],
        'routing/payload-parts' => [
            'what' => 'A payload can be extended by another module without reopening the original route class, so one transport boundary can stay singular while modules stay additive.',
            'how' => 'A base module declares the payload with #[AsPayload]. Another module contributes a trait marked with #[AsPayloadPart(base: ...)]. At runtime PayloadFactory composes a wrapper class that extends the base payload and uses all matching traits.',
            'why' => 'This solves a painful modularity problem: extra request concerns do not force a fork of the original payload and do not leak into untyped arrays. The handler still receives one trusted DTO.',
            'keywords' => [
                ['term' => '#[AsPayloadPart]', 'definition' => 'Marks a trait as an additive extension of an existing payload class.'],
                ['term' => 'PayloadFactory', 'definition' => 'Builds the runtime wrapper class that extends the base payload and mixes in discovered payload-part traits.'],
                ['term' => 'trait composition', 'definition' => 'Lets separate modules add typed setters and getters to the same request boundary without modifying the base payload source.'],
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
            'how' => 'The router uses regex requirements to constrain what each parameter matches. The PayloadHydrator calls setters on the payload DTO with the extracted values. Default values are used when a parameter is not present.',
            'why' => 'Typed path parameters with regex constraints prevent invalid data from reaching handler code. The handler can trust that $payload->slug is always a valid, matched value.',
            'keywords' => [
                ['term' => 'requirements', 'definition' => 'Regex patterns that constrain path parameter values at the routing level.'],
                ['term' => 'PayloadHydrator', 'definition' => 'Populates payload DTO properties by calling setter methods with request data.'],
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
        'di/overview' => [
            'what' => 'Semitexa has one canonical dependency injection model for container-managed framework objects: protected property injection via explicit attributes.',
            'how' => 'The container creates the object, injects configuration and service properties, validates the whole graph at boot, and rejects hidden or competing dependency paths. Readonly services are shared per worker, execution-scoped services are cloned per execution, factories stay explicit, and contracts resolve through declared ownership metadata.',
            'why' => 'The goal is not DI flexibility. The goal is deterministic behavior in a long-running runtime. When the framework uses one visible injection path, boot stays reviewable, graceful reloads stay reliable, and large refactors stop failing because one class quietly used a different dependency pattern.',
            'keywords' => [
                ['term' => 'canonical DI path', 'definition' => 'The single allowed dependency path for container-managed framework objects: explicit attribute-based property injection.'],
                ['term' => 'container-managed framework object', 'definition' => 'A discovered framework class such as a service, repository, handler, or listener that the container instantiates and validates.'],
                ['term' => 'execution-scoped', 'definition' => 'A lifecycle where a fresh clone is used for one HTTP request, console command, or async execution and then discarded.'],
                ['term' => 'boot-time validation', 'definition' => 'The container validates dependency bindings and lifecycle rules during boot so ambiguity fails early and loudly.'],
            ],
        ],
        'di/basic-injection' => [
            'what' => 'Semitexa uses one canonical DI path for container-managed framework objects: explicit property attributes, no constructor wiring, no hidden service lookup.',
            'how' => 'The container instantiates the object first, then hydrates protected properties marked with #[InjectAsReadonly], #[InjectAsMutable], #[InjectAsFactory], or #[Config]. Because every dependency enters through one visible channel, boot validation and static analysis can reject ambiguity before runtime.',
            'why' => 'This is not just stylistic consistency. In a long-running worker, mixed DI styles create boot fragility. A single-path model makes the dependency graph locally readable, easier for LLMs to modify correctly, and much more stable during large refactors and graceful reloads.',
            'keywords' => [
                ['term' => '#[InjectAsReadonly]', 'definition' => 'Injects a service that is built once per worker and shared across executions.'],
                ['term' => '#[InjectAsMutable]', 'definition' => 'Injects an execution-scoped service clone, safe for per-execution state.'],
                ['term' => 'single-path DI', 'definition' => 'Container-managed classes receive dependencies through one explicit attribute-based property model, not a mix of constructors, service locators, and magic context.'],
                ['term' => 'boot fragility', 'definition' => 'A failure mode where one ambiguous or legacy DI pattern breaks container boot, CLI tooling, or worker reload after large changes.'],
            ],
        ],
        'di/contracts' => [
            'what' => 'A service contract is module-owned and explicit: one module declares the capability, and its implementations advertise themselves with #[SatisfiesServiceContract].',
            'how' => 'The container registry resolves contracts at boot from attributes, not string lookups. For keyed factories, Semitexa uses closed-world backed enums so the allowed variants are declared in code and validated exhaustively.',
            'why' => 'This keeps substitution deterministic instead of magical. A reader can see who owns the capability, which implementations exist, and whether the selection space is complete without reverse-engineering runtime behavior.',
            'keywords' => [
                ['term' => '#[SatisfiesServiceContract]', 'definition' => 'Marks a class as the implementation of a service contract interface.'],
                ['term' => 'module-owned capability', 'definition' => 'A contract lives with the module that owns the behavior and ships at least one valid implementation.'],
                ['term' => 'closed-world factory', 'definition' => 'A factory whose selectable implementations are exhaustively declared by a backed enum instead of open-ended strings.'],
            ],
        ],
        'di/scoped-services' => [
            'what' => 'Execution-scoped services are cloned for each framework execution, preventing state leakage across HTTP requests, console runs, and async jobs.',
            'how' => 'The container keeps a readonly tier for shared worker services and an execution-scoped tier for cloned prototypes. #[InjectAsMutable] marks the second case explicitly, and the current execution context is injected only into those declared mutable properties.',
            'why' => 'Long-running workers make lifecycle bugs real. If stateful services accidentally become shared, the bug is cross-request contamination. Explicit execution scope keeps state boundaries reviewable and safe.',
            'keywords' => [
                ['term' => 'Two-tier DI', 'definition' => 'Readonly (worker-shared) + execution-scoped (cloned per execution) container tiers for long-running runtime safety.'],
                ['term' => 'execution-scoped', 'definition' => 'A lifecycle where a fresh instance is used for one HTTP request, console command, or async execution and then discarded.'],
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
            'how' => 'A TableModel maps the table, and an explicit mapper converts between persistence and domain. Repositories then return domain objects on the normal read path.',
            'why' => 'This keeps business code free from ORM metadata and keeps persistence code free from fake business semantics. The boundary stays reviewable and intentional.',
            'keywords' => [
                ['term' => 'TableModel', 'definition' => 'Persistence-side model that maps one table row shape and relation metadata.'],
                ['term' => 'Mapper', 'definition' => 'Explicit converter between TableModel and DomainModel.'],
                ['term' => 'Resource model', 'definition' => 'ORM-facing class responsible for table mapping, columns, indexes, and storage conversion.'],
                ['term' => 'Domain model', 'definition' => 'Business object carrying behavior, invariants, and ubiquitous language without persistence metadata.'],
            ],
        ],
        'data/repository-workflow' => [
            'what' => 'The canonical Semitexa repository workflow is domain-first: application code depends on repository contracts and works with domain models, not raw ORM resources.',
            'how' => 'Repository contracts return domain objects. ORM repository implementations sit behind the contract, convert through explicit mappers, and keep persistence models behind the boundary.',
            'why' => 'This keeps the demo honest about best practices. Resource-level CRUD still exists, but it is not what we should teach as the primary architectural path.',
            'keywords' => [
                ['term' => 'Repository contract', 'definition' => 'Application-facing interface that expresses persistence in domain language.'],
                ['term' => 'Domain-first read path', 'definition' => 'Default repository reads return business models instead of persistence resources.'],
                ['term' => 'DomainRepository', 'definition' => 'New ORM entry point that coordinates TableModel queries, mapping, and aggregate writes.'],
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
            'how' => 'You can model multiple resource abstractions over the same table. A slim list resource selects only the required columns, and the TableModel relation loader batch-loads explicit relations for all hydrated rows in one pass instead of per-row lazy lookups.',
            'why' => 'This removes the usual ORM trade-off between over-fetching giant entities and hiding extra queries behind lazy loading. The fetch plan is explicit, reviewable, and stable under load.',
            'keywords' => [
                ['term' => 'N+1', 'definition' => 'A query anti-pattern where one base query triggers one extra query per returned row.'],
                ['term' => 'TableModelRelationLoader', 'definition' => 'Batch-loads explicit relations once for the whole result set on the TableModel layer.'],
                ['term' => 'Resource slice', 'definition' => 'A narrow resource abstraction over a table containing only the columns and relations required by one use case.'],
            ],
        ],
        'data/repositories' => [
            'what' => 'Repositories provide typed query methods behind domain-facing contracts. The ORM repository is the storage implementation, not the business abstraction.',
            'how' => 'The new ORM path separates TableModel, mapper, and domain model explicitly. Repository code coordinates them and keeps persistence concerns out of handlers.',
            'why' => 'This keeps handlers and services speaking domain language most of the time, while still allowing resource-level operations when persistence details actually matter.',
            'keywords' => [
                ['term' => '#[SatisfiesRepositoryContract]', 'definition' => 'Marks a class as the ORM implementation of a domain repository interface.'],
                ['term' => 'DomainRepository', 'definition' => 'Typed repository surface over TableModel queries and aggregate persistence engine.'],
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
        'auth/session-payloads' => [
            'what' => 'Semitexa treats session state as a typed contract. We do not allow auth state to spread through $this->session->get(\'current_user\') and other string-key guesses.',
            'how' => 'A dedicated class marked with #[SessionSegment] owns the session shape. Handlers read and write that payload through SessionInterface::getPayload() and SessionInterface::setPayload(), while semantic methods such as requireUserId() or clear() live on the payload itself.',
            'why' => 'This kills one of the most persistent sources of auth drift in PHP apps: invisible session conventions. The contract becomes explicit, reviewable, and refactor-safe instead of hiding behind magic key names and duplicated null checks.',
            'keywords' => [
                ['term' => '#[SessionSegment]', 'definition' => 'Declares a typed session payload and binds it to one named session segment.'],
                ['term' => 'Session Payload', 'definition' => 'The explicit DTO-like class that owns one session concern instead of scattering string keys across handlers.'],
                ['term' => 'SessionInterface::getPayload()', 'definition' => 'Typed session access API that hydrates the declared payload instead of returning loose values by string key.'],
                ['term' => 'string-key session chaos', 'definition' => 'The legacy pattern where session contracts exist only as ad hoc names like current_user, auth_user, or user_id.'],
            ],
        ],
        'auth/rbac' => [
            'what' => 'Semitexa RBAC is intentionally hybrid: coarse-grained capabilities can be represented as internal bitmask grants for very fast broad checks, while business-facing permission slugs such as products.write or settings.smtp.update handle exact, human-readable authorization decisions.',
            'how' => 'The payload access policy can declare both #[RequiresCapability] and #[RequiresPermission]. Authorizer evaluates them in order: authentication first, then capability checks, then slug checks. CapabilityRegistry maps Capability enum cases to bit positions inside integer segments, while SubjectGrantResolver builds the current subject grant set and asks a module-level PermissionProviderInterface for the user\'s slug permissions. That means the RBAC core owns the evaluation pipeline, but storage and permission catalogs stay owned by the modules that actually know the business domain.',
            'why' => 'This split avoids two common failures. A pure slug model becomes noisy for broad platform-level rights, while a pure bitmask model becomes opaque for audits and product-specific rules. The hybrid model keeps the hot path compact and machine-friendly, but still gives reviewers and module authors explicit permission names and extension points.',
            'keywords' => [
                ['term' => '#[RequiresCapability]', 'definition' => 'Declares a coarse-grained code-level capability check that is evaluated before slug permissions.'],
                ['term' => 'CapabilityRegistry', 'definition' => 'Maps Capability enum cases to bitmask segment and bit positions so capability checks can stay fast and internal.'],
                ['term' => '#[RequiresPermission]', 'definition' => 'Declares an exact slug-based permission such as users.manage or settings.smtp.update.'],
                ['term' => 'PermissionProviderInterface', 'definition' => 'Contract implemented by domain modules to supply the current user\'s permission slugs without coupling RBAC to a specific storage backend.'],
                ['term' => 'SubjectGrantResolver', 'definition' => 'Builds the authenticated subject\'s combined grant set and caches it per request before Authorizer evaluates policy requirements.'],
                ['term' => 'module-owned permission catalog', 'definition' => 'Each module can define and expose its own permission slugs, roles, and assignment rules while the shared authorization pipeline keeps one evaluation model.'],
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
        'rendering/philosophy' => [
            'what' => 'Semitexa SSR is not a thin “server pre-render” layer. It is a rendering architecture where the page contract, page regions, deferred delivery, live refresh, and component interaction all stay inside one coherent server-owned model.',
            'how' => 'Resource DTOs define the page boundary before Twig renders a single field, slot resources give regions their own response pipeline, deferred blocks stream later HTML instead of triggering client takeover, reactive slots keep re-rendering server truth, and interactive components stay scoped through tiny component-owned JavaScript instead of a page-level UI framework dependency.',
            'why' => 'Most stacks keep the phrase SSR but abandon the idea the moment the page becomes dynamic. Templates start reshaping data, partials start guessing context, and eventually view files begin pulling from storage or external APIs directly. Semitexa treats that drift as a framework problem, not a code review suggestion. It is opinionated because it is trying to preserve one rendering story, one presentation boundary, and one source of truth even when the page becomes late, live, or interactive.',
            'keywords' => [
                ['term' => 'one rendering story', 'definition' => 'The same conceptual model survives first paint, deferred delivery, live refresh, and component interaction.'],
                ['term' => 'server-owned truth', 'definition' => 'The browser displays HTML, but the authoritative rendering contract and state interpretation stay on the server side.'],
                ['term' => 'presentation boundary', 'definition' => 'Templates consume already-shaped response data instead of fetching, reshaping, or interpreting domain data on their own.'],
                ['term' => 'late HTML', 'definition' => 'Deferred content arrives after the shell, but it still arrives as server-rendered HTML rather than a client-side redraw.'],
                ['term' => 'reactive SSR', 'definition' => 'A live region can keep refreshing from server truth while remaining part of the SSR page model.'],
                ['term' => 'interactive SSR component', 'definition' => 'A server-rendered component can declare backend event contracts without turning into a mini SPA island.'],
                ['term' => 'framework-free enhancement', 'definition' => 'Client behavior may exist, but it remains small, scoped, and component-owned instead of becoming a second UI framework layer.'],
            ],
        ],
        'rendering/component-scripts' => [
            'what' => 'A Semitexa SSR component can declare its own optional enhancement asset, so the client behavior belongs to the component contract instead of being manually remembered by the page.',
            'how' => 'Add script to #[AsComponent], let ComponentRenderer auto-require the runtime and the declared asset when that component actually renders, and register the client behavior through SemitexaComponent.register() so the runtime can mount each rendered root.',
            'why' => 'This closes one of the oldest sources of frontend drift: the HTML lives in the component, but the behavior is hidden in page-level includes and boot glue. Semitexa keeps markup and optional enhancement under the same component ownership model.',
            'keywords' => [
                ['term' => 'script', 'definition' => 'Optional AsComponent field that declares the canonical enhancement asset key.'],
                ['term' => 'auto-require', 'definition' => 'The renderer pulls in the runtime and the component asset only when the component is present on the page.'],
                ['term' => 'SemitexaComponent.register()', 'definition' => 'Frontend runtime API that registers one mount function for a named SSR component.'],
                ['term' => 'component root', 'definition' => 'The rendered root element annotated so the runtime can mount behavior per instance.'],
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
        'api/rest-api' => [
            'what' => 'Semitexa REST is explicit and typed. Payload DTOs own the route contract, version markers stay visible, and the response shape remains reviewable instead of drifting into improvised controller code.',
            'how' => 'A REST endpoint is declared on a payload with #[AsPayload], then marked as machine-facing with #[ExternalApi]. Optional concerns such as versioning, sparse fieldsets, expand parameters, and alternative representations stay attached to that same contract instead of being scattered through middleware and controllers.',
            'why' => 'REST should not mean accidental complexity. Semitexa keeps the HTTP surface boring in the best way: one clear route contract, one clear execution path, and machine-facing behavior that is explicit in code review.',
            'keywords' => [
                ['term' => '#[AsPayload]', 'definition' => 'The typed request contract that owns the REST route.'],
                ['term' => '#[ExternalApi]', 'definition' => 'Marks the payload as a public machine-facing REST endpoint.'],
                ['term' => '#[ApiVersion]', 'definition' => 'Attaches explicit lifecycle metadata to a REST contract.'],
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
        'api/graphql-derived' => [
            'what' => 'Semitexa can derive GraphQL-ready operations from the same Payload DTOs that already own REST routes. GraphQL becomes another transport over the same use-case contract instead of a parallel resolver world.',
            'how' => 'A payload opts in with #[ExposeAsGraphql(field: ..., rootType: ..., output: ...)]. The semitexa-graphql package reads discovered Semitexa routes through RouteInspectionRegistryInterface and builds a typed GraphqlOperationRegistry from those declarations. Output contracts stay explicit, so schema generation never has to reverse-engineer presenter arrays or guess field graphs from loose runtime data.',
            'why' => 'This is where the Semitexa shape becomes unusually strong. The framework already has typed request DTOs, handler execution units, and presentation boundaries. GraphQL can therefore be layered on top as a transport concern instead of forcing teams to duplicate business logic in ad-hoc resolver classes.',
            'keywords' => [
                ['term' => '#[ExposeAsGraphql]', 'definition' => 'Explicit opt-in attribute that marks a Payload DTO as safe for GraphQL discovery and declares field name, root type, and output contract.'],
                ['term' => 'GraphqlOperationRegistry', 'definition' => 'Read-only registry of GraphQL-ready Semitexa operations derived from discovered routes.'],
                ['term' => 'typed output contract', 'definition' => 'A dedicated DTO or view class that declares what GraphQL may safely expose.'],
                ['term' => 'resolver drift', 'definition' => 'The common failure mode where GraphQL resolvers quietly become a second application layer with duplicated business logic.'],
            ],
        ],
        'api/graphql' => [
            'what' => 'Semitexa can also serve GraphQL-first APIs. The public entrypoint is still POST /graphql, but the use case behind each field remains explicit through payload and output contracts.',
            'how' => 'A GraphQL-first operation declares #[ExposeAsGraphql(...)] on a dedicated payload and returns a typed output DTO. The transport may be GraphQL-only, but the application structure still avoids the usual resolver sprawl of ad-hoc field classes and improvised arrays.',
            'why' => 'This keeps GraphQL honest. Teams get the graph they want without paying for a second hidden application layer made of resolver glue.',
            'keywords' => [
                ['term' => 'POST /graphql', 'definition' => 'The public transport endpoint used by GraphQL clients.'],
                ['term' => 'GraphQL-first', 'definition' => 'A use case that is exposed only through the graph and does not need a public REST route.'],
                ['term' => 'typed output DTO', 'definition' => 'A concrete class that owns the public GraphQL response shape.'],
            ],
        ],
        'api/rest-graphql' => [
            'what' => 'A single Semitexa use case can answer both REST and GraphQL. The public transports differ, but the business flow stays in one place.',
            'how' => 'An existing REST payload opts into GraphQL with #[ExposeAsGraphql(...)]. REST clients keep calling the normal HTTP route, GraphQL clients call POST /graphql, and both transports reuse the same application contract.',
            'why' => 'This matters when products are not ready to choose one public style forever. Semitexa lets teams support both without forking their use case into two implementations that drift apart over time.',
            'keywords' => [
                ['term' => 'REST + GraphQL', 'definition' => 'One use case exposed through two transports without duplicated handler logic.'],
                ['term' => 'shared contract', 'definition' => 'The same payload and output boundary stays authoritative across both transports.'],
                ['term' => 'transport split', 'definition' => 'REST and GraphQL can differ at the edge while still sharing the same application execution path.'],
            ],
        ],
        'api/sunset-version' => [
            'what' => 'The sunset-version page turns a deprecated API route into a documented lifecycle example instead of dropping users into raw JSON with no framing.',
            'how' => 'The handler precomputes the same collection payload the API would emit and surfaces the version headers alongside the body inside a feature page. That keeps the live response visible while still explaining why Deprecation and Sunset are present.',
            'why' => 'Versioning is not just a payload problem. Teams need to see the response contract, lifecycle headers, and migration signal together in one place.',
            'keywords' => [
                ['term' => 'Deprecation', 'definition' => 'HTTP response header advertising that a version is already on the retirement path.'],
                ['term' => 'Sunset', 'definition' => 'HTTP response header announcing the target retirement date for an API version.'],
                ['term' => 'X-Api-Version', 'definition' => 'Stable response header exposing the semantic version metadata for the current route.'],
            ],
        ],
        'api/active-version' => [
            'what' => 'The active-version page shows the steady-state contract for the current collection endpoint: stable payload, stable header, no retirement noise.',
            'how' => 'Instead of rendering raw JSON directly, the feature page builds the live collection response server-side and presents the body with the key headers and operational notes around it.',
            'why' => 'A healthy API version should be easy to reason about. Consumers should see what stays stable and which metadata they can safely integrate against.',
            'keywords' => [
                ['term' => 'Active lifecycle', 'definition' => 'The version is current, supported, and free from deprecation or sunset warnings.'],
                ['term' => 'X-Api-Version', 'definition' => 'Response header that tells clients exactly which contract version answered the request.'],
            ],
        ],
        'api/structured-errors' => [
            'what' => 'Structured Errors demonstrates that Semitexa API failures stay machine-readable even when the route throws domain exceptions.',
            'how' => 'The route throws typed domain exceptions, and ExternalApiExceptionMapper turns them into one JSON envelope with a stable `error.code`, human message, structured context, and optional retry metadata.',
            'why' => 'API clients need more than a string message. A stable envelope lets SDKs, dashboards, and background jobs branch on error semantics without scraping text.',
            'keywords' => [
                ['term' => 'ExternalApiExceptionMapper', 'definition' => 'Maps domain exceptions on external API routes into stable JSON error envelopes.'],
                ['term' => 'error.context', 'definition' => 'Structured machine-readable metadata that explains the failure without parsing the message.'],
                ['term' => 'request_id', 'definition' => 'Correlation id slot for tracing a failing API request across logs and support channels.'],
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
        'cli/ai-tooling' => [
            'what' => 'Semitexa treats AI operations as part of the CLI contract: capabilities, skills, logs, and assistant entrypoints are exposed deliberately.',
            'how' => 'ai:capabilities publishes command metadata, ai:skills exports the executable skill registry, logs:app supports structured filtering, and the ai command opens the local assistant surface.',
            'why' => 'AI-native workflow is not about sprinkling chat features onto the product. It is about giving agents stable, inspectable operational seams so they can act with less guesswork and less scraping.',
            'keywords' => [
                ['term' => 'ai:capabilities', 'definition' => 'Lists command capabilities with usage guidance, inputs, and output shape for AI tooling.'],
                ['term' => 'ai:skills', 'definition' => 'Exports AI-executable skills with risk, confirmation, and dry-run metadata.'],
                ['term' => 'logs:app', 'definition' => 'Structured application log reader designed to be usable by both humans and LLM agents.'],
            ],
        ],
        'cli/describe-commands' => [
            'what' => 'The CLI can describe the framework graph directly: routes, modules, bindings, and handler invariants are queryable artifacts.',
            'how' => 'describe:route renders the payload-to-template chain, describe:project summarizes modules and listeners, routes:list inventories discovered endpoints, and contracts:list exposes DI bindings.',
            'why' => 'This shortens debugging and onboarding dramatically. Instead of reconstructing framework state by reading scattered attributes and registrations, you ask the system to explain itself.',
            'keywords' => [
                ['term' => 'describe:route', 'definition' => 'Explains one route from payload through handlers, resource, template, and auth posture.'],
                ['term' => 'describe:project', 'definition' => 'Summarizes modules, routes, listeners, and structural counts for the current project.'],
                ['term' => 'contracts:list', 'definition' => 'Shows which implementation is active for each registered service contract.'],
            ],
        ],
        'cli/scaffolding-generators' => [
            'what' => 'Semitexa generators scaffold framework-native files and can also emit machine-readable planning hints for AI-assisted implementation.',
            'how' => 'Commands like make:module, make:page, make:payload, make:service, and make:contract use builders and template resolvers to produce correctly placed files, with dry-run, JSON, and llm-hints modes where appropriate.',
            'why' => 'Good scaffolding is not just about speed. It teaches the expected architecture by generating the right boundaries and naming conventions from the start.',
            'keywords' => [
                ['term' => 'make:page', 'definition' => 'Scaffolds a complete page boundary: payload, handler, resource, and template.'],
                ['term' => '--llm-hints', 'definition' => 'Outputs a machine-readable envelope describing what files were created and what should be implemented next.'],
                ['term' => 'dry-run', 'definition' => 'Lets the user inspect the generation plan before any files are written.'],
            ],
        ],
        'cli/runtime-maintenance' => [
            'what' => 'The framework exposes maintenance commands for code pickup, cache hygiene, generated metadata, linting, and DI probing.',
            'how' => 'server:reload rebuilds autoload and sends a graceful Swoole reload signal, cache:clear removes compiled cache artifacts, registry:sync refreshes generated registry data, and lint/test commands validate architecture and instantiation assumptions.',
            'why' => 'Without a disciplined maintenance surface, teams fall back to ad-hoc deletes, vague restarts, and guesswork. These commands make routine recovery and validation explicit.',
            'keywords' => [
                ['term' => 'server:reload', 'definition' => 'Gracefully reloads Swoole workers so code changes are picked up without a full container restart.'],
                ['term' => 'cache:clear', 'definition' => 'Clears compiled cache artifacts such as Twig output when runtime state becomes stale.'],
                ['term' => 'test:handler', 'definition' => 'Instantiates one handler and reports whether DI properties were wired successfully.'],
            ],
        ],
        'cli/workers-scheduling' => [
            'what' => 'Semitexa CLI also owns the long-running side of the platform: queues, schedules, webhook delivery, mail workers, and tenant-context execution.',
            'how' => 'Dedicated commands expose each stage separately: inspect schedules, plan due runs, start workers, inspect webhook inbox/outbox state, replay deliveries, and run commands in tenant scope.',
            'why' => 'This makes background systems operable. Operators can inspect, intervene, and replay explicitly instead of treating async infrastructure as invisible magic behind the web server.',
            'keywords' => [
                ['term' => 'queue:work', 'definition' => 'Runs the async events worker for queued handlers.'],
                ['term' => 'scheduler:plan', 'definition' => 'Materializes due schedule occurrences into concrete run rows before workers execute them.'],
                ['term' => 'tenant:run', 'definition' => 'Executes any command inside a specific tenant context.'],
            ],
        ],
        'cli/orm-console' => [
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
