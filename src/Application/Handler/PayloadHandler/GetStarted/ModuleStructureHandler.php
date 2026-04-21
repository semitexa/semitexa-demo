<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\GetStarted\ModuleStructurePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPayloadHandler(payload: ModuleStructurePayload::class, resource: DemoFeatureResource::class)]
final class ModuleStructureHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    public function handle(ModuleStructurePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        return $this->projector->project($resource, new FeatureSpec(
            section: 'get-started',
            sectionLabel: 'Start Here',
            slug: 'module-structure',
            entryLine: 'Start with the smallest useful module shape, then expand the system around it instead of hiding the request path under the product shell.',
            learnMoreLabel: 'See the minimal stack →',
            deepDiveLabel: 'See the full module map →',
            relatedSlugs: ['installation', 'beyond-controllers'],
            fallbackTitle: 'Module Structure',
            fallbackSummary: 'The minimal Semitexa module is a typed HTTP spine: payload, handler, resource, and template. The full demo stack adds catalog, shell, and SEO layers on top.',
            fallbackHighlights: ['Payload', 'Handler', 'Resource', 'Template', 'Catalog', 'SEO'],
        ));
    }
}
