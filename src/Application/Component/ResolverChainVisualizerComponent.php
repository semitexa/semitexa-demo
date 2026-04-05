<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attribute\AsComponent;

#[AsComponent(
    name: 'demo-resolver-chain-viz',
    template: '@project-layouts-semitexa-demo/components/resolver-chain-viz.html.twig',
)]
final class ResolverChainVisualizerComponent
{
    public array $strategies = [
        ['name' => 'SubdomainStrategy',   'input' => 'acme.demo.semitexa.dev', 'priority' => 1],
        ['name' => 'HeaderStrategy',      'input' => 'X-Tenant-ID: acme',     'priority' => 2],
        ['name' => 'PathStrategy',        'input' => '/acme/products',         'priority' => 3],
        ['name' => 'QueryParamStrategy',  'input' => '?tenant=acme',           'priority' => 4],
    ];
    public string $resolvedTenant = 'acme';
    public string $resolvedBy = 'HeaderStrategy';
}
