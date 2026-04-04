<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Platform;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Demo\Application\Resource\Response\HasDemoShell;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'demo_tenant_isolation',
    template: '@project-layouts-semitexa-demo/platform/tenancy-isolation.html.twig',
)]
class DemoTenantIsolationResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    public function withDataUnavailable(bool $state): static { return $this->with('dataUnavailable', $state); }
    public function withActiveTenant(string $tenant): static { return $this->with('activeTenant', $tenant); }
    public function withProducts(array $products): static { return $this->with('products', $products); }
    public function withProductCount(int $count): static { return $this->with('productCount', $count); }
    public function withIllustrationSql(string $sql): static { return $this->with('illustrationSql', $sql); }
    public function withAllTenantCounts(array $counts): static { return $this->with('allTenantCounts', $counts); }
}
