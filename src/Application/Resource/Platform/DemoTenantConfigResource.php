<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Platform;

use Semitexa\Core\Attributes\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'demo_tenant_config',
    template: '@project-layouts-semitexa-demo/platform/tenancy-config.html.twig',
)]
class DemoTenantConfigResource extends HtmlResponse implements ResourceInterface
{
    public function withTenantConfigs(array $configs): static { return $this->with('tenantConfigs', $configs); }
    public function withActiveTenant(string $tenant): static { return $this->with('activeTenant', $tenant); }
}
