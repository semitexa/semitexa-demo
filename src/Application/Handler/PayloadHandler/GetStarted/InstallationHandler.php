<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\GetStarted\InstallationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPayloadHandler(payload: InstallationPayload::class, resource: DemoFeatureResource::class)]
final class InstallationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    public function handle(InstallationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        return $this->projector->project($resource, new FeatureSpec(
            section: 'get-started',
            sectionLabel: 'Start Here',
            slug: 'installation',
            entryLine: 'The first useful Semitexa experience should end with a running app and an operator shell you can trust, not with a half-finished checklist.',
            learnMoreLabel: 'See the installation flow →',
            deepDiveLabel: 'What to verify after boot →',
            relatedSlugs: [],
            fallbackTitle: 'Installation',
            fallbackSummary: 'Create the project, review the baseline env contract, and bring up the Semitexa runtime the supported way.',
            fallbackHighlights: ['install.sh', 'bin/semitexa', '.env.default', '.env', 'self-test', 'routes:list'],
        ));
    }
}
