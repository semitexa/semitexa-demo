<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
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
        $tenantIds = $this->tenantConfigProvider->getTenantIds();
        $activeTenant = in_array($payload->getTenant(), $tenantIds, true)
            ? $payload->getTenant()
            : 'acme';

        $configs = array_map(
            fn ($config) => [
                'tenantId'        => $config->tenantId,
                'displayName'     => $config->displayName,
                'primaryColor'    => $config->primaryColor,
                'fontFamily'      => $config->fontFamily,
                'currencyCode'    => $config->currencyCode,
                'ratingStyle'     => $config->ratingStyle,
                'featureFlags'    => $config->featureFlags,
                'supportedLocales' => $config->supportedLocales,
                'defaultLocale'   => $config->defaultLocale,
            ],
            $this->tenantConfigProvider->getAllConfigs()
        );

        return $resource
            ->pageTitle('Per-Tenant Configuration — Semitexa Demo')
            ->withNavSections($this->catalog->getSections())
            ->withFeatureTree($this->catalog->getFeatureTree())
            ->withCurrentSection('platform')
            ->withCurrentSlug('tenancy-config')
            ->withInfoPanel(
                'Each tenant gets its own branding, locale defaults, and feature flags without branching the application.',
                'The active tenant configuration is resolved once, then reused by rendering and downstream services.',
                'A convincing platform demo needs to show that tenancy changes the product feel, not only the database rows.',
            )
            ->withTenantConfigs($configs)
            ->withActiveTenant($activeTenant);
    }
}
