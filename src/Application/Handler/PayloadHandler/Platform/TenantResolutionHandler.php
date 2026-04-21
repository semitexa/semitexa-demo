<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantResolutionPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantResolutionResource;

#[AsPayloadHandler(payload: TenantResolutionPayload::class, resource: DemoTenantResolutionResource::class)]
final class TenantResolutionHandler implements TypedHandlerInterface
{
    private const DOC_KEYWORDS = ['HeaderStrategy', 'SubdomainStrategy', 'PathStrategy', 'QueryParamStrategy', 'resolver chain'];

    private const STRATEGIES = [
        ['id' => 'header',    'name' => 'HeaderStrategy',     'description' => 'Reads X-Tenant-ID request header.',               'example' => 'X-Tenant-ID: acme',            'priority' => 2, 'bestFor' => 'API gateways and internal service calls',              'tradeoff' => 'Only works when upstream systems reliably forward the tenant header.',            'tenant' => 'acme'],
        ['id' => 'subdomain', 'name' => 'SubdomainStrategy',  'description' => 'Extracts tenant from the subdomain.',              'example' => 'acme.demo.semitexa.dev',       'priority' => 1, 'bestFor' => 'White-label apps and branded customer entrypoints',       'tradeoff' => 'Requires DNS and routing setup for each tenant-facing host.',                      'tenant' => 'acme'],
        ['id' => 'path',      'name' => 'PathStrategy',       'description' => 'Reads the first path segment as tenant ID.',       'example' => '/acme/products',               'priority' => 3, 'bestFor' => 'Admin consoles and shared hosts where subdomains are not practical', 'tradeoff' => 'URLs stay explicit, but tenant identity becomes part of every visible path.', 'tenant' => 'globex'],
        ['id' => 'query',     'name' => 'QueryParamStrategy', 'description' => 'Reads the ?tenant= query parameter.',              'example' => '?tenant=acme',                 'priority' => 4, 'bestFor' => 'Testing, debugging, and temporary operator tools',        'tradeoff' => 'Useful for diagnostics, but usually too weak as the primary production entrypoint.', 'tenant' => 'initech'],
    ];

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    public function handle(TenantResolutionPayload $payload, DemoTenantResolutionResource $resource): DemoTenantResolutionResource
    {
        $strategies = self::STRATEGIES;
        usort($strategies, static fn ($a, $b) => $a['priority'] <=> $b['priority']);

        $selected = $this->findSelectedStrategy($strategies, $payload->getTab() ?? 'header');

        $spec = new FeatureSpec(
            section: 'platform',
            slug: 'tenancy-resolution',
            entryLine: 'See how Semitexa resolves the active tenant from subdomain, header, path, or query input before the rest of the platform runs.',
            learnMoreLabel: 'Try it yourself →',
            deepDiveLabel: 'Under the hood →',
            relatedSlugs: [],
            fallbackTitle: 'Tenant Context Resolution',
            fallbackSummary: 'See how Semitexa resolves the active tenant from subdomain, header, path, or query input before the rest of the platform runs.',
            fallbackHighlights: self::DOC_KEYWORDS,
            explanation: [
                'what' => 'Semitexa decides the active tenant before configuration, data access, queues, and rendering continue downstream.',
                'how' => 'The resolver chain tries the configured strategies in priority order. The first match wins and becomes the tenant context for the rest of the execution.',
                'why' => 'If tenant resolution is ambiguous, every “isolated” layer above it becomes unreliable. That is why this boundary deserves explicit design.',
                'keywords' => self::DOC_KEYWORDS,
            ],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $this->projector->project($resource, $spec);

        return $resource
            ->withStrategies($strategies)
            ->withActiveTab($selected['id'])
            ->withResolvedTenant($selected['tenant'])
            ->withResolvedBy($selected['name']);
    }

    /**
     * @param list<array<string, mixed>> $strategies
     * @return array<string, mixed>
     */
    private function findSelectedStrategy(array $strategies, string $activeTab): array
    {
        foreach ($strategies as $strategy) {
            if ($strategy['id'] === $activeTab) {
                return $strategy;
            }
        }

        return $strategies[0];
    }
}
