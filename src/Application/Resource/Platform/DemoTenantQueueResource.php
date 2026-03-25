<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Platform;

use Semitexa\Core\Attributes\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'demo_tenant_queue',
    template: '@project-layouts-semitexa-demo/platform/tenancy-queue.html.twig',
)]
class DemoTenantQueueResource extends HtmlResponse implements ResourceInterface
{
    public function withSerializedPayload(array $payload): static { return $this->with('serializedPayload', $payload); }
    public function withTenantContext(array $tenantContext): static { return $this->with('tenantContext', $tenantContext); }
    public function withWorkerSteps(array $steps): static { return $this->with('workerSteps', $steps); }
}
