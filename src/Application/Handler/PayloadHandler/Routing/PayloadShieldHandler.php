<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Routing\PayloadShieldPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PayloadShieldPayload::class, resource: DemoFeatureResource::class)]
final class PayloadShieldHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(PayloadShieldPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'routing',
            slug: 'payload-shield',
            entryLine: 'A payload is the one trusted boundary: external data is normalized inside setters before application code runs.',
            learnMoreLabel: 'See the boundary in code →',
            deepDiveLabel: 'How the shield works →',
            relatedSlugs: [],
            fallbackTitle: 'Payload As A Shield',
            fallbackSummary: 'Hydration happens before the handler, and each setter owns the normalization and guard logic for its own field.',
            fallbackHighlights: ['PayloadHydrator', 'ValidationException', 'setter guards', '422 before handler'],
            explanation: $this->explanationProvider->getExplanation('routing', 'payload-shield') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/payload-shield-showcase.html.twig', [
                'painPoints' => [
                    'Raw request arrays make controllers mix transport parsing, validation, and business rules in one method.',
                    'The same checks get repeated across handlers because there is no single boundary object that owns input truth.',
                    'When invalid data slips through, the handler must keep defending itself instead of focusing on the business action.',
                ],
                'pipeline' => [
                    ['stage' => 'Hydrate', 'detail' => 'PayloadHydrator maps request input into one payload object via typed setters.'],
                    ['stage' => 'Normalize', 'detail' => 'Setter code trims, casts, and shapes the input for the field it owns.'],
                    ['stage' => 'Guard', 'detail' => 'Setter-level checks throw a field-aware ValidationException before invalid data can reach the handler.'],
                    ['stage' => 'Handle', 'detail' => 'Business code receives a trusted DTO and can focus on intent, not defensive parsing.'],
                ],
                'compare' => [
                    [
                        'variant' => 'warning',
                        'eyebrow' => 'Scattered Responsibility',
                        'title' => 'Raw request in the handler',
                        'summary' => 'Input extraction, branching, validation errors, and business rules all compete in one controller method.',
                        'note' => 'The boundary is blurry, so every handler keeps re-checking input just in case.',
                    ],
                    [
                        'variant' => 'active',
                        'eyebrow' => 'Single Source Of Truth',
                        'title' => 'Payload as the shield',
                        'summary' => 'The payload owns hydration and field guards, so the handler receives clean data it can trust.',
                        'note' => 'Single responsibility becomes obvious: setters guard input, handler executes the use case.',
                    ],
                ],
                'signals' => [
                    ['value' => '1', 'label' => 'trusted boundary object'],
                    ['value' => '422', 'label' => 'automatic invalid-input rejection'],
                    ['value' => '0', 'label' => 'transport checks left in the handler'],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/payload-shield-rules.html.twig', [
                'rules' => [
                    'The payload is the single place where external data becomes internal application data.',
                    'Setter signatures define the accepted shape, and setter code defines the accepted business constraints.',
                    'If the payload is invalid, the request ends before the handler is called.',
                    'Handlers should read like use cases, not like defensive transport parsers.',
                ],
                'checks' => [
                    ['label' => 'Hydration', 'detail' => 'PayloadHydrator calls typed setters on the payload DTO.'],
                    ['label' => 'Field guards', 'detail' => 'Setters throw field-aware exceptions when incoming values are not acceptable.'],
                    ['label' => 'Trust boundary', 'detail' => 'Once the handler runs, the payload should already be safe to consume.'],
                ],
            ])
            ->withSourceCode([
                'Typical Controller' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PayloadShield/LegacyCheckoutController.example.php'),
                'Shield Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PayloadShield/CreateCheckoutPayload.example.php'),
                'Shield Handler' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PayloadShield/CreateCheckoutHandler.example.php'),
            ]);
    }
}
