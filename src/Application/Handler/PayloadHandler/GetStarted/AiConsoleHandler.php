<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\GetStarted\AiConsolePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPayloadHandler(payload: AiConsolePayload::class, resource: DemoFeatureResource::class)]
final class AiConsoleHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    public function handle(AiConsolePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        return $this->projector->project($resource, new FeatureSpec(
            section: 'get-started',
            sectionLabel: 'Start Here',
            slug: 'ai-console',
            entryLine: 'For common maintenance and discovery work you can ask the CLI in plain language instead of recalling every exact command from memory.',
            learnMoreLabel: 'See the AI console flow →',
            deepDiveLabel: 'When to use it and when not to →',
            relatedSlugs: [],
            fallbackTitle: 'AI Console',
            fallbackSummary: 'Use `bin/semitexa ai` as an alternative CLI entrypoint when you do not want to remember exact command names.',
            fallbackHighlights: ['bin/semitexa ai', 'natural-language prompts', 'experimental', 'command translation'],
        ));
    }
}
