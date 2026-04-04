<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\DiOverviewPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DiOverviewPayload::class, resource: DemoFeatureResource::class)]
final class DiOverviewHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DiOverviewPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('di', 'overview') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Payload' => $this->sourceCodeReader->readClassSource(DiOverviewPayload::class),
        ];

        return $resource
            ->pageTitle('DI Canon — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'di',
                'currentSlug' => 'overview',
                'infoWhat' => $explanation['what'] ?? 'Semitexa uses one canonical DI path for container-managed framework objects.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('di')
            ->withSlug('overview')
            ->withTitle('DI Canon')
            ->withSummary('One canonical DI path for container-managed classes: explicit properties, explicit lifecycles, deterministic boot.')
            ->withEntryLine('One canonical DI path for container-managed classes: explicit properties, explicit lifecycles, deterministic boot.')
            ->withHighlights(['single-path DI', '#[InjectAsReadonly]', '#[InjectAsMutable]', 'boot-time validation'])
            ->withLearnMoreLabel('See the Semitexa canon →')
            ->withDeepDiveLabel('Why mixed DI fails →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Semitexa Canon',
                'title' => 'One path per concern',
                'summary' => 'The container-managed model is deliberately narrow so boot, tooling, and reload behavior stay deterministic even after large cross-package changes.',
                'columns' => ['Concern', 'Canonical path', 'Why it exists'],
                'rows' => [
                    [
                        ['text' => 'Service dependency'],
                        ['text' => '#[InjectAsReadonly] or #[InjectAsMutable]', 'code' => true],
                        ['text' => 'A dependency is visible where the class uses it.'],
                    ],
                    [
                        ['text' => 'Scalar config'],
                        ['text' => '#[Config]', 'code' => true],
                        ['text' => 'Configuration stops leaking through constructors or magic env reads.'],
                    ],
                    [
                        ['text' => 'Lifecycle'],
                        ['text' => 'worker-shared or execution-scoped'],
                        ['text' => 'State boundaries stay explicit in a long-running worker model.'],
                    ],
                    [
                        ['text' => 'Variant selection'],
                        ['text' => 'contract metadata or closed-world factory'],
                        ['text' => 'Selection stays reviewable instead of becoming runtime magic.'],
                    ],
                ],
                'paragraphs' => [
                    'Semitexa does not optimize for infinite DI flexibility. It optimizes for clarity under change.',
                    'That tradeoff matters because the same application must survive worker reuse, graceful reload, static analysis, and large LLM-assisted refactors without hidden dependency channels.',
                ],
                'note' => 'The rest of the DI section shows each piece of this canon in isolation: readonly services, execution scope, factories, and contracts.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
