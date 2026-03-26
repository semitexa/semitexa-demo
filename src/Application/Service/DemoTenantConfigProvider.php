<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Demo\Domain\Contract\DemoTenantConfigProviderInterface;
use Semitexa\Demo\Domain\Model\DemoTenantConfig;

/**
 * Hard-coded demo tenant configurations — no DB dependency.
 * Acme Corp (blue, serif, USD), Globex Inc (green, modern, EUR), Initech Ltd (orange, minimal, UAH).
 */
#[AsService]
final class DemoTenantConfigProvider implements DemoTenantConfigProviderInterface
{
    /** @var array<string, DemoTenantConfig> */
    private array $configs;

    public function __construct()
    {
        $this->configs = [
            'acme' => new DemoTenantConfig(
                tenantId: 'acme',
                displayName: 'Acme Corp',
                primaryColor: '#1e40af',
                fontFamily: 'Georgia, serif',
                logoPath: '/demo/static/tenants/acme-logo.svg',
                currencyCode: 'USD',
                ratingStyle: 'stars',
                featureFlags: [
                    'reviews_enabled'  => true,
                    'wishlist_enabled' => true,
                    'ai_chat_enabled'  => false,
                ],
                supportedLocales: ['en', 'de', 'uk'],
                defaultLocale: 'en',
            ),
            'globex' => new DemoTenantConfig(
                tenantId: 'globex',
                displayName: 'Globex Inc',
                primaryColor: '#166534',
                fontFamily: '"Inter", sans-serif',
                logoPath: '/demo/static/tenants/globex-logo.svg',
                currencyCode: 'EUR',
                ratingStyle: 'numeric',
                featureFlags: [
                    'reviews_enabled'  => true,
                    'wishlist_enabled' => false,
                    'ai_chat_enabled'  => true,
                ],
                supportedLocales: ['en', 'de'],
                defaultLocale: 'de',
            ),
            'initech' => new DemoTenantConfig(
                tenantId: 'initech',
                displayName: 'Initech Ltd',
                primaryColor: '#c2410c',
                fontFamily: '"DM Sans", system-ui, sans-serif',
                logoPath: '/demo/static/tenants/initech-logo.svg',
                currencyCode: 'UAH',
                ratingStyle: 'stars-half',
                featureFlags: [
                    'reviews_enabled'  => false,
                    'wishlist_enabled' => true,
                    'ai_chat_enabled'  => false,
                ],
                supportedLocales: ['en', 'uk'],
                defaultLocale: 'uk',
            ),
        ];
    }

    public function getConfig(string $tenantId): ?DemoTenantConfig
    {
        return $this->configs[$tenantId] ?? null;
    }

    public function getAllConfigs(): array
    {
        return array_values($this->configs);
    }

    public function getTenantIds(): array
    {
        return array_keys($this->configs);
    }
}
