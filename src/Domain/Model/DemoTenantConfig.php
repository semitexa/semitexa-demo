<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Model;

/**
 * Value object representing per-tenant branding and configuration.
 * Three demo tenants ship with the package — no database needed.
 */
final readonly class DemoTenantConfig
{
    public function __construct(
        public string $tenantId,        // 'acme', 'globex', 'initech'
        public string $displayName,     // 'Acme Corp'
        public string $primaryColor,    // '#1e40af'
        public string $fontFamily,      // 'Georgia, serif'
        public string $logoPath,        // '@project-static-Demo/tenants/acme-logo.svg'
        public string $currencyCode,    // 'USD'
        public string $ratingStyle,     // 'stars' | 'numeric' | 'stars-half'
        public array $featureFlags,     // ['reviews_enabled' => true, 'wishlist_enabled' => false]
        public array $supportedLocales, // ['en', 'de', 'uk']
        public string $defaultLocale,   // 'en'
    ) {}
}
