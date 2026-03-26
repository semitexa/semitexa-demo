<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
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
                'tenant'       => $config->tenantId,
                'displayName'  => $config->displayName,
                'organization' => $config->tenantId,
                'theme'        => $config->tenantId . '-theme',
                'locale'       => $config->defaultLocale,
                'color'        => $config->primaryColor,
            ];
        }

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
            ->withMatrix($matrix);
    }
}
