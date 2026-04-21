<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\GetStarted\BaseTenantPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPayloadHandler(payload: BaseTenantPayload::class, resource: DemoFeatureResource::class)]
final class BaseTenantHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    public function handle(BaseTenantPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        return $this->projector->project($resource, new FeatureSpec(
            section: 'get-started',
            sectionLabel: 'Start Here',
            slug: 'base-tenant',
            entryLine: 'The first tenant is configuration, not ceremony: define it in `.env`, register the host, restart, and open the tenant like a real product surface.',
            learnMoreLabel: 'See the tenant bootstrap flow →',
            deepDiveLabel: 'How Semitexa resolves that tenant →',
            relatedSlugs: [],
            fallbackTitle: 'Base Tenant',
            fallbackSummary: 'Define the first tenant through environment variables and resolve it through a real local host.',
            fallbackHighlights: ['tenant', 'tenant context', 'tenant config', 'default tenant'],
        ));
    }
}
