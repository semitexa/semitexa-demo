<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantLayersPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantLayersResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoTenantConfigProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: TenantLayersPayload::class, resource: DemoTenantLayersResource::class)]
final class TenantLayersHandler implements TypedHandlerInterface
{
    private const LAYERS = [
        [
            'name'        => 'OrganizationLayer',
            'description' => 'Identifies which tenant (organization) owns this request.',
            'resolves'    => 'tenant_id: "acme"',
            'strategy'    => 'OrganizationStrategy',
            'order'       => 1,
        ],
        [
            'name'        => 'LocaleLayer',
            'description' => 'Determines the preferred language for this tenant request.',
            'resolves'    => 'locale: "en"',
            'strategy'    => 'LocaleStrategy',
            'order'       => 2,
        ],
        [
            'name'        => 'ThemeLayer',
            'description' => 'Loads per-tenant branding — colors, fonts, feature flags.',
            'resolves'    => 'theme: "acme-blue"',
            'strategy'    => 'ThemeStrategy',
            'order'       => 3,
        ],
        [
            'name'        => 'EnvironmentLayer',
            'description' => 'Identifies the deployment stage for this tenant.',
            'resolves'    => 'env: "production"',
            'strategy'    => 'EnvironmentStrategy',
            'order'       => 4,
        ],
    ];

    #[InjectAsReadonly]
    protected DemoTenantConfigProvider $tenantConfigProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(TenantLayersPayload $payload, DemoTenantLayersResource $resource): DemoTenantLayersResource
    {
        $configs = $this->tenantConfigProvider->getAllConfigs();

        // Build Organization × Theme matrix
        $matrix = [];
        foreach ($configs as $config) {
            $matrix[] = [
                'tenant'       => $config->getTenantId(),
                'displayName'  => $config->getDisplayName(),
                'organization' => $config->getTenantId(),
                'theme'        => $config->getTenantId() . '-theme',
                'locale'       => $config->getDefaultLocale(),
                'color'        => $config->getPrimaryColor(),
            ];
        }

        $layerHighlights = [
            [
                'title' => 'Resolve organization first',
                'detail' => 'The runtime must decide whose request this is before any other tenant-aware behavior makes sense.',
            ],
            [
                'title' => 'Derive locale and theme independently',
                'detail' => 'Language and branding are separate concerns. One can change without forcing the other to be hard-wired.',
            ],
            [
                'title' => 'Keep environment as a layer, not a hidden global',
                'detail' => 'Production, staging, or preview behavior should be explicit in the tenant context instead of leaking through conditionals.',
            ],
        ];

        $resolverPrinciples = [
            [
                'title' => 'Each layer owns one question',
                'detail' => 'Organization answers who the request belongs to. Locale answers how the product speaks. Theme answers how it looks. Environment answers where it runs.',
            ],
            [
                'title' => 'Strategies stay swappable',
                'detail' => 'You can change how locale resolves without reopening organization or theme logic, because each strategy is isolated.',
            ],
            [
                'title' => 'Consumers read one composed context',
                'detail' => 'Templates and handlers should consume the final TenantContext, not reconstruct layer decisions themselves.',
            ],
        ];

        $layerOutcome = [
            'title' => 'What the composed context gives you',
            'items' => [
                'A stable tenant identifier for isolation and ownership checks.',
                'Locale defaults that shape copy, formatting, and translation lookups.',
                'Theme decisions that control colors, fonts, and product feel.',
                'Environment metadata that can influence integrations and deployment behavior.',
            ],
        ];

        return $resource
            ->pageTitle('Multi-Layer Tenancy — Semitexa Demo')
            ->withNavSections($this->catalog->getSections())
            ->withFeatureTree($this->catalog->getFeatureTree())
            ->withCurrentSection('platform')
            ->withCurrentSlug('tenancy-layers')
            ->withInfoPanel(
                'Tenant context is not one switch. It is a composed stack of organization, locale, theme, and environment decisions.',
                'Each layer resolves independently, then merges into the final context consumed by the rest of the app.',
                'Showing the layers separately makes the platform model understandable instead of mystical.',
            )
            ->withLayers(self::LAYERS)
            ->withMatrix($matrix)
            ->withLayerHighlights($layerHighlights)
            ->withResolverPrinciples($resolverPrinciples)
            ->withLayerOutcome($layerOutcome);
    }
}
