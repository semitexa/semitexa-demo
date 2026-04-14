<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantConfigPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantConfigResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoTenantConfigProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: TenantConfigPayload::class, resource: DemoTenantConfigResource::class)]
final class TenantConfigHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoTenantConfigProvider $tenantConfigProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(TenantConfigPayload $payload, DemoTenantConfigResource $resource): DemoTenantConfigResource
    {
        $providerConfigs = $this->tenantConfigProvider->getAllConfigs();
        $tenantIds = $this->tenantConfigProvider->getTenantIds();
        $activeTenant = in_array($payload->getTenant(), $tenantIds, true)
            ? $payload->getTenant()
            : 'acme';

        $tenantNarratives = [
            'acme' => 'Acme reads like a premium catalog: serif typography, blue brand color, reviews and wishlist enabled, English first.',
            'globex' => 'Globex feels more operational and conversion-focused: green UI, numeric ratings, German default locale, AI chat enabled.',
            'initech' => 'Initech stays lighter and more utilitarian: orange accent, Ukrainian default locale, wishlist enabled, reviews disabled.',
        ];

        $configs = array_map(
            fn ($config) => [
                'tenantId'        => $config->getTenantId(),
                'displayName'     => $config->getDisplayName(),
                'primaryColor'    => $config->getPrimaryColor(),
                'fontFamily'      => $config->getFontFamily(),
                'currencyCode'    => $config->getCurrencyCode(),
                'ratingStyle'     => $config->getRatingStyle(),
                'featureFlags'    => $config->getFeatureFlags(),
                'supportedLocales' => $config->getSupportedLocales(),
                'defaultLocale'   => $config->getDefaultLocale(),
                'isActive'        => $config->getTenantId() === $activeTenant,
                'href'            => '/demo/platform/tenancy/config?tenant=' . $config->getTenantId(),
                'narrative'       => $tenantNarratives[$config->getTenantId()] ?? 'Tenant-specific configuration changes the product surface without branching handlers.',
            ],
            $providerConfigs
        );

        $activeConfig = null;
        foreach ($providerConfigs as $config) {
            if ($config->getTenantId() !== $activeTenant) {
                continue;
            }

            $featureFlags = $config->getFeatureFlags();
            $activeConfig = [
                'tenantId' => $config->getTenantId(),
                'displayName' => $config->getDisplayName(),
                'primaryColor' => $config->getPrimaryColor(),
                'fontFamily' => $config->getFontFamily(),
                'currencyCode' => $config->getCurrencyCode(),
                'ratingStyle' => $config->getRatingStyle(),
                'defaultLocale' => $config->getDefaultLocale(),
                'supportedLocales' => $config->getSupportedLocales(),
                'featureFlags' => $featureFlags,
                'logoPath' => $config->getLogoPath(),
                'narrative' => $tenantNarratives[$config->getTenantId()] ?? '',
                'visibleOutcomes' => [
                    sprintf('Branding resolves to %s with %s typography.', $config->getPrimaryColor(), $config->getFontFamily()),
                    sprintf('Prices and commerce copy can default to %s and locale %s.', $config->getCurrencyCode(), $config->getDefaultLocale()),
                    sprintf('Rating widgets switch to %s presentation.', $config->getRatingStyle()),
                    sprintf(
                        'Feature flags: reviews %s, wishlist %s, AI chat %s.',
                        ($featureFlags['reviews_enabled'] ?? false) ? 'on' : 'off',
                        ($featureFlags['wishlist_enabled'] ?? false) ? 'on' : 'off',
                        ($featureFlags['ai_chat_enabled'] ?? false) ? 'on' : 'off',
                    ),
                ],
            ];
            break;
        }

        $comparisonRows = [
            [
                'label' => 'Brand surface',
                'explanation' => 'What a user notices first when the tenant changes.',
                'values' => [
                    'acme' => 'Blue serif storefront with premium tone',
                    'globex' => 'Green sans-serif workspace with utilitarian tone',
                    'initech' => 'Orange minimal UI with lighter product voice',
                ],
            ],
            [
                'label' => 'Commerce defaults',
                'explanation' => 'What downstream UI components can assume without passing tenant IDs around.',
                'values' => [
                    'acme' => 'USD, locales en/de/uk, default en',
                    'globex' => 'EUR, locales en/de, default de',
                    'initech' => 'UAH, locales en/uk, default uk',
                ],
            ],
            [
                'label' => 'Interaction rules',
                'explanation' => 'Which features appear as product capabilities rather than hard-coded if/else branches.',
                'values' => [
                    'acme' => 'Reviews on, wishlist on, AI chat off',
                    'globex' => 'Reviews on, wishlist off, AI chat on',
                    'initech' => 'Reviews off, wishlist on, AI chat off',
                ],
            ],
            [
                'label' => 'Rendering implication',
                'explanation' => 'How the same handler/template tree can still feel like a different product.',
                'values' => [
                    'acme' => 'Editorial catalog feel',
                    'globex' => 'Operational B2B dashboard feel',
                    'initech' => 'Lean regional product feel',
                ],
            ],
        ];

        $resolutionSteps = [
            [
                'title' => 'Select the tenant once',
                'detail' => 'This demo chooses the active tenant from the query parameter and resolves a single tenant config object.',
            ],
            [
                'title' => 'Build the tenant-facing layers',
                'detail' => 'Theme, locale defaults, rating style, and feature flags are derived from that config instead of being scattered across controllers or templates.',
            ],
            [
                'title' => 'Let handlers and views consume capabilities',
                'detail' => 'Downstream code asks for the active layer or capability and renders the correct experience without comparing tenant IDs manually.',
            ],
        ];

        return $resource
            ->pageTitle('Per-Tenant Configuration — Semitexa Demo')
            ->withNavSections($this->catalog->getSections())
            ->withFeatureTree($this->catalog->getFeatureTree())
            ->withCurrentSection('platform')
            ->withCurrentSlug('tenancy-config')
            ->withInfoPanel(
                'This page demonstrates that tenancy is not only row isolation. The active tenant changes branding, locale defaults, pricing conventions, and visible features.',
                'One tenant config is resolved once, then reused by rendering, component behavior, and downstream services.',
                'The important platform promise is this: the same codebase can produce multiple product surfaces without sprinkling tenant-specific if/else logic everywhere.',
            )
            ->withTenantConfigs($configs)
            ->withActiveTenant($activeTenant)
            ->withActiveTenantConfig($activeConfig ?? [])
            ->withComparisonRows($comparisonRows)
            ->withResolutionSteps($resolutionSteps);
    }
}
