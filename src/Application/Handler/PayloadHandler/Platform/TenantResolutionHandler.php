<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantResolutionPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantResolutionResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: TenantResolutionPayload::class, resource: DemoTenantResolutionResource::class)]
final class TenantResolutionHandler implements TypedHandlerInterface
{
    private const STRATEGIES = [
        [
            'id'          => 'header',
            'name'        => 'HeaderStrategy',
            'description' => 'Reads X-Tenant-ID request header.',
            'example'     => 'X-Tenant-ID: acme',
            'priority'    => 2,
            'bestFor'     => 'API gateways and internal service calls',
            'tradeoff'    => 'Only works when upstream systems reliably forward the tenant header.',
            'tenant'      => 'acme',
        ],
        [
            'id'          => 'subdomain',
            'name'        => 'SubdomainStrategy',
            'description' => 'Extracts tenant from the subdomain.',
            'example'     => 'acme.demo.semitexa.dev',
            'priority'    => 1,
            'bestFor'     => 'White-label apps and branded customer entrypoints',
            'tradeoff'    => 'Requires DNS and routing setup for each tenant-facing host.',
            'tenant'      => 'acme',
        ],
        [
            'id'          => 'path',
            'name'        => 'PathStrategy',
            'description' => 'Reads the first path segment as tenant ID.',
            'example'     => '/acme/products',
            'priority'    => 3,
            'bestFor'     => 'Admin consoles and shared hosts where subdomains are not practical',
            'tradeoff'    => 'URLs stay explicit, but tenant identity becomes part of every visible path.',
            'tenant'      => 'globex',
        ],
        [
            'id'          => 'query',
            'name'        => 'QueryParamStrategy',
            'description' => 'Reads the ?tenant= query parameter.',
            'example'     => '?tenant=acme',
            'priority'    => 4,
            'bestFor'     => 'Testing, debugging, and temporary operator tools',
            'tradeoff'    => 'Useful for diagnostics, but usually too weak as the primary production entrypoint.',
            'tenant'      => 'initech',
        ],
    ];

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(TenantResolutionPayload $payload, DemoTenantResolutionResource $resource): DemoTenantResolutionResource
    {
        $activeTab = $payload->getTab() ?? 'header';
        $strategies = self::STRATEGIES;

        usort($strategies, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        $selected = null;
        foreach ($strategies as $strategy) {
            if ($strategy['id'] === $activeTab) {
                $selected = $strategy;
                break;
            }
        }

        $selected ??= $strategies[0];

        return $resource
            ->pageTitle('Tenant Context Resolution — Semitexa Demo')
            ->withNavSections($this->catalog->getSections())
            ->withFeatureTree($this->catalog->getFeatureTree())
            ->withCurrentSection('platform')
            ->withCurrentSlug('tenancy-resolution')
            ->withInfoPanel(
                'Semitexa decides the active tenant before configuration, data access, queues, and rendering continue downstream.',
                'The resolver chain tries the configured strategies in priority order. The first match wins and becomes the tenant context for the rest of the execution.',
                'If tenant resolution is ambiguous, every “isolated” layer above it becomes unreliable. That is why this boundary deserves explicit design.',
            )
            ->withStrategies($strategies)
            ->withActiveTab($activeTab)
            ->withResolvedTenant($selected['tenant'])
            ->withResolvedBy($selected['name']);
    }
}
