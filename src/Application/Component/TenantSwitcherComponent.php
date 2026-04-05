<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attribute\AsComponent;

#[AsComponent(
    name: 'demo-tenant-switcher',
    template: '@project-layouts-semitexa-demo/components/tenant-switcher.html.twig',
)]
final class TenantSwitcherComponent
{
    public string $activeTenant = 'acme';
    public array $tenants = [
        ['id' => 'acme',    'label' => 'Acme',    'color' => '#1e40af'],
        ['id' => 'globex',  'label' => 'Globex',  'color' => '#166534'],
        ['id' => 'initech', 'label' => 'Initech', 'color' => '#c2410c'],
    ];
}
