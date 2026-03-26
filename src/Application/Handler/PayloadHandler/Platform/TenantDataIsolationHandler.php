<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantDataIsolationPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantIsolationResource;
use Semitexa\Demo\Application\Service\DemoTenantConfigProvider;
use Semitexa\Demo\Application\Service\DemoTenantDataSeeder;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: TenantDataIsolationPayload::class, resource: DemoTenantIsolationResource::class)]
final class TenantDataIsolationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoTenantDataSeeder $tenantDataSeeder;

    #[InjectAsReadonly]
    protected DemoTenantConfigProvider $tenantConfigProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(TenantDataIsolationPayload $payload, DemoTenantIsolationResource $resource): DemoTenantIsolationResource
    {
        $tenantIds = $this->tenantConfigProvider->getTenantIds();
        $activeTenant = in_array($payload->getTenant(), $tenantIds, true)
            ? $payload->getTenant()
            : 'acme';

        $products = $this->tenantDataSeeder->getProducts($activeTenant, 5);
        $count = $this->tenantDataSeeder->getProductCount($activeTenant);
        $sql = $this->tenantDataSeeder->getIllustrationSql($activeTenant);

        $allCounts = [];
        foreach ($tenantIds as $tenantId) {
            $allCounts[$tenantId] = $this->tenantDataSeeder->getProductCount($tenantId);
        }

        return $resource
            ->pageTitle('Data Isolation — Semitexa Demo')
            ->withActiveTenant($activeTenant)
            ->withProducts(array_map(fn ($p) => [
                'name'   => $p->name ?? '—',
                'price'  => $p->price ?? '0.00',
                'status' => $p->status ?? 'active',
            ], $products))
            ->withProductCount($count)
            ->withIllustrationSql($sql)
            ->withAllTenantCounts($allCounts);
    }
}
