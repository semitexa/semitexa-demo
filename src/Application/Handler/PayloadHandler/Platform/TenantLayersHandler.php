<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantLayersPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantLayersResource;
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
            ->withLayers(self::LAYERS)
            ->withMatrix($matrix);
    }
}
