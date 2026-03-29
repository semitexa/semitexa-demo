<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\PayloadShieldPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PayloadShieldPayload::class, resource: DemoFeatureResource::class)]
final class PayloadShieldHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(PayloadShieldPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('routing', 'payload-shield') ?? [];

        return $resource
            ->pageTitle('Payload As A Shield — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'payload-shield',
                'infoWhat' => $explanation['what'] ?? 'A payload is the trusted boundary between raw HTTP input and application logic.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('payload-shield')
            ->withTitle('Payload As A Shield')
            ->withSummary('Hydration, type casting, and validation happen before the handler, so business code receives one trusted object instead of raw external input.')
            ->withEntryLine('A payload is the one trusted boundary: external data is normalized and validated before application code runs.')
            ->withHighlights(['ValidatablePayload', 'RequestDtoHydrator', 'PayloadValidator', '422 before handler'])
            ->withLearnMoreLabel('See the boundary in code →')
            ->withDeepDiveLabel('How the shield works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/payload-shield-showcase.html.twig', [
                'painPoints' => [
                    'Raw request arrays make controllers mix transport parsing, validation, and business rules in one method.',
                    'The same checks get repeated across handlers because there is no single boundary object that owns input truth.',
                    'When invalid data slips through, the handler must keep defending itself instead of focusing on the business action.',
                ],
                'pipeline' => [
                    ['stage' => 'Hydrate', 'detail' => 'RequestDtoHydrator maps request input into one payload object via typed setters.'],
                    ['stage' => 'Cast', 'detail' => 'Setter parameter types normalize external strings into the shapes the app expects.'],
                    ['stage' => 'Validate', 'detail' => 'PayloadValidator runs validate() and returns 422 before the handler on invalid input.'],
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
                        'summary' => 'The payload owns hydration and validation, so the handler receives clean data it can trust.',
                        'note' => 'Single responsibility becomes obvious: payload guards input, handler executes the use case.',
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
                    'Setter signatures define the accepted shape, and validate() defines the accepted business constraints.',
                    'If the payload is invalid, the request ends before the handler is called.',
                    'Handlers should read like use cases, not like defensive transport parsers.',
                ],
                'checks' => [
                    ['label' => 'Hydration', 'detail' => 'RequestDtoHydrator calls typed setters on the payload DTO.'],
                    ['label' => 'Validation', 'detail' => 'ValidatablePayload::validate() returns field errors in one consistent format.'],
                    ['label' => 'Trust boundary', 'detail' => 'Once the handler runs, the payload should already be safe to consume.'],
                ],
            ])
            ->withSourceCode([
                'Typical Controller' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Routing/PayloadShield/LegacyCheckoutController.example.php'),
                'Shield Payload' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Routing/PayloadShield/CreateCheckoutPayload.example.php'),
                'Shield Handler' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Routing/PayloadShield/CreateCheckoutHandler.example.php'),
            ])
            ->withExplanation($explanation);
    }
}
