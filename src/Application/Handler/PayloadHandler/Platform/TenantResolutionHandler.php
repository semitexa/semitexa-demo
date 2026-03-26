<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantResolutionPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantResolutionResource;
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
        ],
        [
            'id'          => 'subdomain',
            'name'        => 'SubdomainStrategy',
            'description' => 'Extracts tenant from the subdomain.',
            'example'     => 'acme.demo.semitexa.dev',
            'priority'    => 1,
        ],
        [
            'id'          => 'path',
            'name'        => 'PathStrategy',
            'description' => 'Reads the first path segment as tenant ID.',
            'example'     => '/acme/products',
            'priority'    => 3,
        ],
        [
            'id'          => 'query',
            'name'        => 'QueryParamStrategy',
            'description' => 'Reads the ?tenant= query parameter.',
            'example'     => '?tenant=acme',
            'priority'    => 4,
        ],
    ];

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(TenantResolutionPayload $payload, DemoTenantResolutionResource $resource): DemoTenantResolutionResource
    {
        $activeTab = $payload->getTab() ?? 'header';
        $strategies = self::STRATEGIES;

        usort($strategies, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $resource
            ->pageTitle('Tenant Resolution Strategies — Semitexa Demo')
            ->withStrategies($strategies)
            ->withActiveTab($activeTab)
            ->withResolvedTenant('acme')
            ->withResolvedBy('HeaderStrategy');
    }
}
