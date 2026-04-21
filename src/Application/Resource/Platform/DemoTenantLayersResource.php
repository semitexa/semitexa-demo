<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Platform;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsResource(
    handle: 'demo_tenant_layers',
    template: '@project-layouts-semitexa-demo/platform/tenancy-layers.html.twig',
)]
class DemoTenantLayersResource extends DemoFeatureResource implements ResourceInterface
{
    public function withLayers(array $layers): static { return $this->with('layers', $layers); }
    public function withMatrix(array $matrix): static { return $this->with('matrix', $matrix); }
    public function withLayerHighlights(array $highlights): static { return $this->with('layerHighlights', $highlights); }
    public function withResolverPrinciples(array $principles): static { return $this->with('resolverPrinciples', $principles); }
    public function withLayerOutcome(array $outcome): static { return $this->with('layerOutcome', $outcome); }
}
