<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Log\LoggerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantDataIsolationPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantIsolationResource;
use Semitexa\Demo\Application\Service\DemoTenantConfigProvider;
use Semitexa\Demo\Application\Service\DemoTenantDataSeeder;
use Throwable;

#[AsPayloadHandler(payload: TenantDataIsolationPayload::class, resource: DemoTenantIsolationResource::class)]
final class TenantDataIsolationHandler implements TypedHandlerInterface
{
    private const DOC_KEYWORDS = ['tenant_id', 'data isolation', 'automatic filtering', 'repository boundary', 'shared storage'];

    private const ISOLATION_HIGHLIGHTS = [
        [
            'title' => 'Switch tenant, keep the same query',
            'detail' => 'The repository method does not change when the tenant changes. Only the active context changes.',
        ],
        [
            'title' => 'Filtering belongs to the platform layer',
            'detail' => 'The tenant WHERE clause is injected by the framework, so business queries are not polluted with isolation boilerplate.',
        ],
        [
            'title' => 'The result should feel obviously different',
            'detail' => 'A convincing demo must show that each tenant sees a distinct slice of data, not just a hidden architecture rule.',
        ],
    ];

    private const ISOLATION_STRATEGIES = [
        ['name' => 'same_storage', 'summary' => 'Shared table, automatic WHERE tenant_id = ?'],
        ['name' => 'connection_switch', 'summary' => 'Separate database per tenant, connection swapped on context change'],
        ['name' => 'separate_schema', 'summary' => 'Separate schema per tenant, search-path switching'],
    ];

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoTenantDataSeeder $tenantDataSeeder;

    #[InjectAsReadonly]
    protected DemoTenantConfigProvider $tenantConfigProvider;

    #[InjectAsReadonly]
    protected LoggerInterface $logger;

    public function handle(TenantDataIsolationPayload $payload, DemoTenantIsolationResource $resource): DemoTenantIsolationResource
    {
        $tenantIds = $this->tenantConfigProvider->getTenantIds();
        $activeTenant = in_array($payload->getTenant(), $tenantIds, true) ? $payload->getTenant() : 'acme';
        [$dataUnavailable, $products, $count, $allCounts] = $this->loadTenantData($activeTenant, $tenantIds);
        $sql = $this->tenantDataSeeder->getIllustrationSql($activeTenant);

        $spec = new FeatureSpec(
            section: 'platform',
            slug: 'tenancy-isolation',
            entryLine: 'Product listing scoped by tenant — switch tenant, list changes. Zero manual WHERE clauses.',
            learnMoreLabel: 'Try it yourself →',
            deepDiveLabel: 'Under the hood →',
            relatedSlugs: [],
            fallbackTitle: 'Data Isolation',
            fallbackSummary: 'Product listing scoped by tenant — switch tenant, list changes. Zero manual WHERE clauses.',
            fallbackHighlights: self::DOC_KEYWORDS,
            explanation: [
                'what' => 'Switch tenant, and the same repository calls return a different dataset without hand-written WHERE clauses.',
                'how' => 'Tenant-scoped resources inject tenant filters automatically, so repository code stays focused on business queries.',
                'why' => 'This is the kind of platform guarantee that should be obvious in a demo, not hidden in docs.',
                'keywords' => self::DOC_KEYWORDS,
            ],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $this->projector->project($resource, $spec);

        return $resource
            ->withDataUnavailable($dataUnavailable)
            ->withActiveTenant($activeTenant)
            ->withActiveTenantSummary($this->buildActiveTenantSummary($activeTenant, $count))
            ->withProducts(array_map(
                static fn ($p): array => ['name' => $p->getName(), 'price' => $p->getPrice(), 'status' => $p->getStatus()],
                $products,
            ))
            ->withProductCount($count)
            ->withIllustrationSql($sql)
            ->withAllTenantCounts($allCounts)
            ->withTenantTabs($this->buildTenantTabs($tenantIds, $activeTenant, $allCounts))
            ->withIsolationHighlights(self::ISOLATION_HIGHLIGHTS)
            ->withIsolationStrategies(self::ISOLATION_STRATEGIES);
    }

    /**
     * @param list<string> $tenantIds
     * @return array{0: bool, 1: list<object>, 2: int, 3: array<string, int>}
     */
    private function loadTenantData(string $activeTenant, array $tenantIds): array
    {
        try {
            $products = $this->tenantDataSeeder->getProducts($activeTenant, 5);
            $count = $this->tenantDataSeeder->getProductCount($activeTenant);
            $allCounts = [];
            foreach ($tenantIds as $tenantId) {
                $allCounts[$tenantId] = $this->tenantDataSeeder->getProductCount($tenantId);
            }

            return [false, $products, $count, $allCounts];
        } catch (Throwable $exception) {
            $this->logger->warning('Demo tenant isolation data unavailable', [
                'active_tenant' => $activeTenant,
                'tenant_ids' => $tenantIds,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return [true, [], 0, array_fill_keys($tenantIds, 0)];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildActiveTenantSummary(string $activeTenant, int $count): array
    {
        $config = $this->tenantConfigProvider->getConfig($activeTenant);

        return [
            'tenantId' => $activeTenant,
            'displayName' => $config?->getDisplayName() ?? strtoupper($activeTenant),
            'color' => $config?->getPrimaryColor() ?? '#6b7280',
            'defaultLocale' => $config?->getDefaultLocale() ?? 'en',
            'currencyCode' => $config?->getCurrencyCode() ?? 'USD',
            'count' => $count,
            'narrative' => match ($activeTenant) {
                'acme' => 'Acme should only see its own catalog rows even though the repository call stays the same.',
                'globex' => 'Globex resolves a different tenant context and the dataset shifts without reopening query code.',
                'initech' => 'Initech proves the same repository contract can safely return a smaller tenant-specific slice.',
                default => 'The active tenant changes the dataset automatically at the persistence boundary.',
            },
        ];
    }

    /**
     * @param list<string> $tenantIds
     * @param array<string, int> $allCounts
     * @return list<array<string, mixed>>
     */
    private function buildTenantTabs(array $tenantIds, string $activeTenant, array $allCounts): array
    {
        $tabs = [];
        foreach ($tenantIds as $tenantId) {
            $config = $this->tenantConfigProvider->getConfig($tenantId);
            $tabs[] = [
                'tenantId' => $tenantId,
                'displayName' => $config?->getDisplayName() ?? strtoupper($tenantId),
                'count' => $allCounts[$tenantId] ?? 0,
                'href' => '?tenant=' . $tenantId,
                'isActive' => $tenantId === $activeTenant,
                'color' => $config?->getPrimaryColor() ?? '#6b7280',
            ];
        }

        return $tabs;
    }
}
