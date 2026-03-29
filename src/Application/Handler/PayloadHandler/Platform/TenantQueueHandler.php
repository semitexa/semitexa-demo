<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Platform;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Platform\TenantQueuePayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantQueueResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoTenantConfigProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: TenantQueuePayload::class, resource: DemoTenantQueueResource::class)]
final class TenantQueueHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoTenantConfigProvider $tenantConfigProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(TenantQueuePayload $payload, DemoTenantQueueResource $resource): DemoTenantQueueResource
    {
        $tenantIds = $this->tenantConfigProvider->getTenantIds();
        $activeTenant = in_array($payload->getTenant(), $tenantIds, true)
            ? $payload->getTenant()
            : 'acme';

        $config = $this->tenantConfigProvider->getConfig($activeTenant);

        $tenantContext = [
            'org'      => $activeTenant,
            'locale'   => $config?->defaultLocale ?? 'en',
            'theme'    => $activeTenant . '-theme',
            'env'      => 'production',
        ];

        // Illustrative serialized queue payload
        $serializedPayload = [
            'action'     => 'generate_report',
            'product_id' => 42,
            '_tenant'    => $tenantContext,
        ];

        $workerSteps = [
            ['step' => 1, 'label' => 'Worker receives message',       'detail' => 'Queue consumer dequeues job payload'],
            ['step' => 2, 'label' => 'TenantAwareJobSerializer::unwrap()', 'detail' => 'Extracts _tenant key, strips it from clean payload'],
            ['step' => 3, 'label' => 'TenantContext::set($context)',   'detail' => 'Context restored — ORM, templates, queues all scoped'],
            ['step' => 4, 'label' => 'Handler runs with tenant scope', 'detail' => 'Queries hit tenant_id = \'' . $activeTenant . '\' automatically'],
        ];

        return $resource
            ->pageTitle('Queue Tenant Propagation — Semitexa Demo')
            ->withNavSections($this->catalog->getSections())
            ->withFeatureTree($this->catalog->getFeatureTree())
            ->withCurrentSection('platform')
            ->withCurrentSlug('tenancy-queue')
            ->withInfoPanel(
                'Queued jobs keep tenant context attached so background work stays scoped after the HTTP request is gone.',
                'The serializer wraps the message with a tenant envelope, and the worker restores that context before executing the job.',
                'Without this, multi-tenant background processing quietly becomes dangerous.',
            )
            ->withSerializedPayload($serializedPayload)
            ->withTenantContext($tenantContext)
            ->withWorkerSteps($workerSteps);
    }
}
