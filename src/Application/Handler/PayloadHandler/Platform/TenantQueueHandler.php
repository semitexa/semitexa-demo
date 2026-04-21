<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantQueuePayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantQueueResource;
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
            entryLine: 'Tenant context travels with queued jobs — _tenant key injected automatically, restored by worker.',
            learnMoreLabel: 'Try it yourself →',
            deepDiveLabel: 'Under the hood →',
            relatedSlugs: [],
            fallbackTitle: 'Queue Tenant Propagation',
            fallbackSummary: 'Tenant context travels with queued jobs — _tenant key injected automatically, restored by worker.',
            fallbackHighlights: self::DOC_KEYWORDS,
            explanation: [
                'what' => 'Queued jobs keep tenant context attached so background work stays scoped after the HTTP request is gone.',
                'how' => 'The serializer wraps the message with a tenant envelope, and the worker restores that context before executing the job.',
                'why' => 'Without this, multi-tenant background processing quietly becomes dangerous.',
                'keywords' => self::DOC_KEYWORDS,
            ],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $this->projector->project($resource, $spec);

        return $resource
            ->withSerializedPayload([
                'action' => 'generate_report',
                'product_id' => 42,
                '_tenant' => $tenantContext,
            ])
            ->withTenantContext($tenantContext)
            ->withWorkerSteps([
                ['step' => 1, 'label' => 'Worker receives message',             'detail' => 'Queue consumer dequeues job payload'],
                ['step' => 2, 'label' => 'TenantAwareJobSerializer::unwrap()', 'detail' => 'Extracts _tenant key, strips it from clean payload'],
                ['step' => 3, 'label' => 'TenantContext::set($context)',        'detail' => 'Context restored — ORM, templates, queues all scoped'],
                ['step' => 4, 'label' => 'Handler runs with tenant scope',      'detail' => 'Queries hit tenant_id = \'' . $activeTenant . '\' automatically'],
            ]);
    }
}
