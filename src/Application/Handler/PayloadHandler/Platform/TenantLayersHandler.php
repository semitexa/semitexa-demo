<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantLayersPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantLayersResource;
use Semitexa\Demo\Application\Service\DemoTenantConfigProvider;

#[AsPayloadHandler(payload: TenantLayersPayload::class, resource: DemoTenantLayersResource::class)]
final class TenantLayersHandler implements TypedHandlerInterface
{
    private const DOC_KEYWORDS = ['OrganizationLayer', 'LocaleLayer', 'ThemeLayer', 'EnvironmentLayer', 'TenantContext'];

    private const LAYERS = [
        ['name' => 'OrganizationLayer', 'description' => 'Identifies which tenant (organization) owns this request.', 'resolves' => 'tenant_id: "acme"', 'strategy' => 'OrganizationStrategy', 'order' => 1],
        ['name' => 'LocaleLayer',       'description' => 'Determines the preferred language for this tenant request.', 'resolves' => 'locale: "en"',       'strategy' => 'LocaleStrategy',       'order' => 2],
        ['name' => 'ThemeLayer',        'description' => 'Loads per-tenant branding — colors, fonts, feature flags.',  'resolves' => 'theme: "acme-blue"',  'strategy' => 'ThemeStrategy',        'order' => 3],
        ['name' => 'EnvironmentLayer',  'description' => 'Identifies the deployment stage for this tenant.',           'resolves' => 'env: "production"',   'strategy' => 'EnvironmentStrategy',  'order' => 4],
    ];

    private const LAYER_HIGHLIGHTS = [
        ['title' => 'Resolve organization first', 'detail' => 'The runtime must decide whose request this is before any other tenant-aware behavior makes sense.'],
        ['title' => 'Derive locale and theme independently', 'detail' => 'Language and branding are separate concerns. One can change without forcing the other to be hard-wired.'],
        ['title' => 'Keep environment as a layer, not a hidden global', 'detail' => 'Production, staging, or preview behavior should be explicit in the tenant context instead of leaking through conditionals.'],
    ];

    private const RESOLVER_PRINCIPLES = [
        ['title' => 'Each layer owns one question', 'detail' => 'Organization answers who the request belongs to. Locale answers how the product speaks. Theme answers how it looks. Environment answers where it runs.'],
        ['title' => 'Strategies stay swappable', 'detail' => 'You can change how locale resolves without reopening organization or theme logic, because each strategy is isolated.'],
        ['title' => 'Consumers read one composed context', 'detail' => 'Templates and handlers should consume the final TenantContext, not reconstruct layer decisions themselves.'],
    ];

    private const LAYER_OUTCOME = [
        'title' => 'What the composed context gives you',
        'items' => [
            'A stable tenant identifier for isolation and ownership checks.',
            'Locale defaults that shape copy, formatting, and translation lookups.',
            'Theme decisions that control colors, fonts, and product feel.',
            'Environment metadata that can influence integrations and deployment behavior.',
        ],
    ];

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoTenantConfigProvider $tenantConfigProvider;

    public function handle(TenantLayersPayload $payload, DemoTenantLayersResource $resource): DemoTenantLayersResource
    {
        $spec = new FeatureSpec(
            section: 'platform',
            slug: 'tenancy-layers',
            entryLine: 'Organization, Locale, Theme, Environment — four independent layers compose into one TenantContext.',
            learnMoreLabel: 'Try it yourself →',
            deepDiveLabel: 'Under the hood →',
            relatedSlugs: [],
            fallbackTitle: 'Multi-Layer Tenancy',
            fallbackSummary: 'Organization, Locale, Theme, Environment — four independent layers compose into one TenantContext.',
            fallbackHighlights: self::DOC_KEYWORDS,
            explanation: [
                'what' => 'Tenant context is not one switch. It is a composed stack of organization, locale, theme, and environment decisions.',
                'how' => 'Each layer resolves independently, then merges into the final context consumed by the rest of the app.',
                'why' => 'Showing the layers separately makes the platform model understandable instead of mystical.',
                'keywords' => self::DOC_KEYWORDS,
            ],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $this->projector->project($resource, $spec);

        return $resource
            ->withLayers(self::LAYERS)
            ->withMatrix($this->buildMatrix())
            ->withLayerHighlights(self::LAYER_HIGHLIGHTS)
            ->withResolverPrinciples(self::RESOLVER_PRINCIPLES)
            ->withLayerOutcome(self::LAYER_OUTCOME);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildMatrix(): array
    {
        $matrix = [];
        foreach ($this->tenantConfigProvider->getAllConfigs() as $config) {
            $matrix[] = [
                'tenant' => $config->getTenantId(),
                'displayName' => $config->getDisplayName(),
                'organization' => $config->getTenantId(),
                'theme' => $config->getTenantId() . '-theme',
                'locale' => $config->getDefaultLocale(),
                'color' => $config->getPrimaryColor(),
            ];
        }

        return $matrix;
    }
}
