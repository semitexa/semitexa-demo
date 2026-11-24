<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Service\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Service\Feature\FeatureExplanation;
use Semitexa\Demo\Application\Service\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantQueuePayload;
use Semitexa\Demo\Application\Resource\Response\Platform\DemoTenantQueueResource;
use Semitexa\Demo\Application\Service\DemoTenantConfigProvider;

#[AsPayloadHandler(payload: TenantQueuePayload::class, resource: DemoTenantQueueResource::class)]
final class TenantQueueHandler implements TypedHandlerInterface
{
    private const DOC_KEYWORDS = ['TenantAwareJobSerializer', '_tenant envelope', 'queue propagation', 'worker context', 'background isolation'];

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoTenantConfigProvider $tenantConfigProvider;

    public function handle(TenantQueuePayload $payload, DemoTenantQueueResource $resource): DemoTenantQueueResource
    {
        $tenantIds = $this->tenantConfigProvider->getTenantIds();
        $activeTenant = in_array($payload->getTenant(), $tenantIds, true) ? $payload->getTenant() : 'acme';
        $config = $this->tenantConfigProvider->getConfig($activeTenant);

        $tenantContext = [
            'org' => $activeTenant,
            'locale' => $config?->getDefaultLocale() ?? 'en',
            'theme' => $activeTenant . '-theme',
            'env' => 'production',
        ];

        $spec = new FeatureSpec(
            section: 'platform',
            slug: 'tenancy-queue',
            entryLine: 'The core queue does not carry the tenant. A Queued handler takes the tenant id from its payload and enters that tenant itself.',
            learnMoreLabel: 'Try it yourself →',
            deepDiveLabel: 'Under the hood →',
            relatedSlugs: [],
            fallbackTitle: 'Tenants in Queued Jobs',
            fallbackSummary: 'The core queue does not carry the tenant. A Queued handler or listener runs with no tenant, so the job carries the tenant id in its payload and enters that tenant itself.',
            fallbackHighlights: self::DOC_KEYWORDS,
            explanation: FeatureExplanation::fromArray([
                'what' => 'The core queue carries no tenant. A Queued handler or listener that queue:work runs sees the default scope, not the tenant of the request that queued the job.',
                'how' => 'Put a tenantId in the queued payload and enter that tenant in the handler through TenantContextStore. TenantAwareJobSerializer is a helper for a package that owns both its dispatcher and its worker. In the framework only media uses it.',
                'why' => 'A job that runs in the default scope reads and writes the wrong data without failing. Nothing warns about it.',
            ]),
            pageTitleSuffix: ' | Semitexa Demo',
        );

        $this->projector->project($resource, $spec);

        return $resource
            ->withSerializedPayload([
                'action' => 'generate_report',
                'product_id' => 42,
                // The keys TenantContext::forSerialization() writes for wrap().
                '_tenant' => [
                    'tenantId' => $tenantContext['org'],
                    'strategy' => 'subdomain',
                    'locale' => $tenantContext['locale'],
                    'environment' => 'prod',
                ],
            ])
            ->withTenantContext($tenantContext)
            ->withWorkerSteps([
                ['step' => 1, 'label' => 'The package\'s own worker receives the message', 'detail' => 'For example media:work. The core queue:work does not do the next steps'],
                ['step' => 2, 'label' => 'TenantAwareJobSerializer::unwrapAndRestore($data)', 'detail' => 'Removes the _tenant key and returns the clean payload'],
                ['step' => 3, 'label' => 'TenantContextStore::shared()->setFallback($context)', 'detail' => 'Called inside unwrapAndRestore(). The fallback is read only outside a coroutine'],
                ['step' => 4, 'label' => 'The job runs with the tenant context', 'detail' => 'TenantContext::get()?->getTenantId() returns \'' . $activeTenant . '\''],
            ]);
    }
}
