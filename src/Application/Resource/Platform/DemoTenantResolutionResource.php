<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Platform;

use Semitexa\Core\Attributes\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'demo_tenant_resolution',
    template: '@project-layouts-semitexa-demo/platform/tenancy-resolution.html.twig',
)]
class DemoTenantResolutionResource extends HtmlResponse implements ResourceInterface
{
    public function withStrategies(array $strategies): static { return $this->with('strategies', $strategies); }
    public function withResolvedTenant(string $tenant): static { return $this->with('resolvedTenant', $tenant); }
    public function withResolvedBy(string $strategyName): static { return $this->with('resolvedBy', $strategyName); }
    public function withActiveTab(string $tab): static { return $this->with('activeTab', $tab); }
}
