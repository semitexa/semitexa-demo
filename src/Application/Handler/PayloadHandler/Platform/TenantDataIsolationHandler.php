<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Log\LoggerInterface;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantDataIsolationPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantIsolationResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoTenantConfigProvider;
use Semitexa\Demo\Application\Service\DemoTenantDataSeeder;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Throwable;

#[AsPayloadHandler(payload: TenantDataIsolationPayload::class, resource: DemoTenantIsolationResource::class)]
final class TenantDataIsolationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoTenantDataSeeder $tenantDataSeeder;

    #[InjectAsReadonly]
    protected DemoTenantConfigProvider $tenantConfigProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected LoggerInterface $logger;

    public function handle(TenantDataIsolationPayload $payload, DemoTenantIsolationResource $resource): DemoTenantIsolationResource
    {
        $configs = $this->tenantConfigProvider->getAllConfigs();
        $tenantIds = $this->tenantConfigProvider->getTenantIds();
        $activeTenant = in_array($payload->getTenant(), $tenantIds, true)
            ? $payload->getTenant()
            : 'acme';

        $dataUnavailable = false;
        $products = [];
        $count = 0;
        $sql = $this->tenantDataSeeder->getIllustrationSql($activeTenant);
        $allCounts = [];

        try {
            $products = $this->tenantDataSeeder->getProducts($activeTenant, 5);
            $count = $this->tenantDataSeeder->getProductCount($activeTenant);

            foreach ($tenantIds as $tenantId) {
                $allCounts[$tenantId] = $this->tenantDataSeeder->getProductCount($tenantId);
            }
        } catch (Throwable $exception) {
            $this->logger->warning('Demo tenant isolation data unavailable', [
                'active_tenant' => $activeTenant,
                'tenant_ids' => $tenantIds,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            $dataUnavailable = true;

            foreach ($tenantIds as $tenantId) {
                $allCounts[$tenantId] = 0;
            }
        }

        $tenantConfigMap = [];
        foreach ($configs as $config) {
            $tenantConfigMap[$config->getTenantId()] = $config;
        }

        $tenantTabs = [];
        foreach ($tenantIds as $tenantId) {
            $config = $tenantConfigMap[$tenantId] ?? null;
            $tenantTabs[] = [
                'tenantId' => $tenantId,
                'displayName' => $config?->getDisplayName() ?? strtoupper($tenantId),
                'count' => $allCounts[$tenantId] ?? 0,
                'href' => '?tenant=' . $tenantId,
                'isActive' => $tenantId === $activeTenant,
                'color' => $config?->getPrimaryColor() ?? '#6b7280',
            ];
        }

        $activeConfig = $tenantConfigMap[$activeTenant] ?? null;
        $activeTenantSummary = [
            'tenantId' => $activeTenant,
            'displayName' => $activeConfig?->getDisplayName() ?? strtoupper($activeTenant),
            'color' => $activeConfig?->getPrimaryColor() ?? '#6b7280',
            'defaultLocale' => $activeConfig?->getDefaultLocale() ?? 'en',
            'currencyCode' => $activeConfig?->getCurrencyCode() ?? 'USD',
            'count' => $count,
            'narrative' => match ($activeTenant) {
                'acme' => 'Acme should only see its own catalog rows even though the repository call stays the same.',
                'globex' => 'Globex resolves a different tenant context and the dataset shifts without reopening query code.',
                'initech' => 'Initech proves the same repository contract can safely return a smaller tenant-specific slice.',
                default => 'The active tenant changes the dataset automatically at the persistence boundary.',
            },
        ];

        $isolationHighlights = [
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

        $isolationStrategies = [
            [
                'name' => 'same_storage',
                'summary' => 'Shared table, automatic WHERE tenant_id = ?',
            ],
            [
                'name' => 'connection_switch',
                'summary' => 'Separate database per tenant, connection swapped on context change',
            ],
            [
                'name' => 'separate_schema',
                'summary' => 'Separate schema per tenant, search-path switching',
            ],
        ];

        return $resource
            ->pageTitle('Data Isolation — Semitexa Demo')
            ->withNavSections($this->catalog->getSections())
            ->withFeatureTree($this->catalog->getFeatureTree())
            ->withCurrentSection('platform')
            ->withCurrentSlug('tenancy-isolation')
            ->withInfoPanel(
                'Switch tenant, and the same repository calls return a different dataset without hand-written WHERE clauses.',
                'Tenant-scoped resources inject tenant filters automatically, so repository code stays focused on business queries.',
                'This is the kind of platform guarantee that should be obvious in a demo, not hidden in docs.',
            )
            ->withDataUnavailable($dataUnavailable)
            ->withActiveTenant($activeTenant)
            ->withActiveTenantSummary($activeTenantSummary)
            ->withProducts(array_map(fn ($p) => [
                'name'   => $p->getName(),
                'price'  => $p->getPrice(),
                'status' => $p->getStatus(),
            ], $products))
            ->withProductCount($count)
            ->withIllustrationSql($sql)
            ->withAllTenantCounts($allCounts)
            ->withTenantTabs($tenantTabs)
            ->withIsolationHighlights($isolationHighlights)
            ->withIsolationStrategies($isolationStrategies);
    }
}
