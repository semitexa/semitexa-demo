<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attribute\AsComponent;

#[AsComponent(
    name: 'demo-tenant-brand-preview',
    template: '@project-layouts-semitexa-demo/components/tenant-brand-preview.html.twig',
)]
final class TenantBrandPreviewComponent
{
    public string $tenantId = 'acme';
    public string $displayName = 'Acme Corp';
    public string $primaryColor = '#1e40af';
    public string $fontFamily = 'Georgia, serif';
    public string $currencyCode = 'USD';
    public string $ratingStyle = 'stars';
    public array $featureFlags = [];
    public array $supportedLocales = [];
}
