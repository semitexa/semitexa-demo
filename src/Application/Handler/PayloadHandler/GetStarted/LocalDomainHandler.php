<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\GetStarted\LocalDomainPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPayloadHandler(payload: LocalDomainPayload::class, resource: DemoFeatureResource::class)]
final class LocalDomainHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    public function handle(LocalDomainPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        return $this->projector->project($resource, new FeatureSpec(
            section: 'get-started',
            sectionLabel: 'Start Here',
            slug: 'local-domain',
            entryLine: 'A framework with tenancy should not be introduced through localhost forever. Register a stable local domain early and let the runtime behave like a product host.',
            learnMoreLabel: 'See the local domain flow →',
            deepDiveLabel: 'Why domain-first local work matters →',
            relatedSlugs: [],
            fallbackTitle: 'Local Domain',
            fallbackSummary: 'Register `.test` domains through the built-in local-domain helper instead of relying on ad-hoc host setup.',
            fallbackHighlights: ['TENANCY_BASE_DOMAIN', 'bin/semitexa local-domain:add', 'local-domain:list', 'server:restart'],
        ));
    }
}
