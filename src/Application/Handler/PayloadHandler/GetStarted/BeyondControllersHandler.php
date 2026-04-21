<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\GetStarted\BeyondControllersPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: BeyondControllersPayload::class, resource: DemoFeatureResource::class)]
final class BeyondControllersHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(BeyondControllersPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'get-started',
            sectionLabel: 'Start Here',
            slug: 'beyond-controllers',
            entryLine: 'If one class owns the route, request parsing, validation, auth assumptions, business orchestration, and response assembly, it stops being simple and starts being the hidden coupling point of the whole feature.',
            learnMoreLabel: 'See why controllers stop scaling →',
            deepDiveLabel: 'How the Semitexa split stays reviewable →',
            relatedSlugs: [],
            fallbackTitle: 'Beyond Controllers',
            fallbackSummary: 'Controller-first design bundles too many responsibilities into one unstable class. Semitexa splits the transport contract, the use case, and the response shape deliberately.',
            fallbackHighlights: ['Payload owns slug contract', 'Handler owns use case', 'Resource owns response shape', 'No controller glue'],
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Payload' => $this->sourceCodeReader->readProjectRelativeSource(
                    'resources/examples/GetStarted/ProductShowcasePayload.example.php',
                ),
                'Handler' => $this->sourceCodeReader->readProjectRelativeSource(
                    'resources/examples/GetStarted/ProductShowcaseHandler.example.php',
                ),
                'Resource' => $this->sourceCodeReader->readProjectRelativeSource(
                    'resources/examples/GetStarted/ProductShowcaseResource.example.php',
                ),
                'Legacy Controller Example' => $this->sourceCodeReader->readProjectRelativeSource(
                    'resources/examples/GetStarted/LegacyProductShowController.example.php',
                ),
            ]);
    }
}
