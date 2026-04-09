<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Platform;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Demo\Application\Resource\Response\HasDemoShell;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'demo_tenant_config',
    template: '@project-layouts-semitexa-demo/platform/tenancy-config.html.twig',
)]
class DemoTenantConfigResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    public function withTenantConfigs(array $configs): static { return $this->with('tenantConfigs', $configs); }
    public function withActiveTenant(string $tenant): static { return $this->with('activeTenant', $tenant); }
    public function withActiveTenantConfig(array $config): static { return $this->with('activeTenantConfig', $config); }
    public function withComparisonRows(array $rows): static { return $this->with('comparisonRows', $rows); }
    public function withResolutionSteps(array $steps): static { return $this->with('resolutionSteps', $steps); }
}
