<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\ContentNegotiationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ContentNegotiationPayload::class, resource: DemoFeatureResource::class)]
final class ContentNegotiationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    private const PRODUCTS = [
        ['id' => '1', 'name' => 'Wireless Headphones', 'price' => 79.99],
        ['id' => '2', 'name' => 'Mechanical Keyboard', 'price' => 129.99],
        ['id' => '3', 'name' => 'Ultra-wide Monitor', 'price' => 549.99],
    ];

    public function handle(ContentNegotiationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('routing', 'content-negotiation') ?? [];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(ContentNegotiationPayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        $jsonPreview = json_encode(['products' => self::PRODUCTS], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';

        return $resource
            ->pageTitle('Content Negotiation — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'content-negotiation',
                'infoWhat' => $explanation['what'] ?? 'One endpoint, multiple response formats — automatically.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('content-negotiation')
            ->withTitle('Content Negotiation')
            ->withSummary('One endpoint, multiple response formats — automatically.')
            ->withEntryLine('One endpoint serves JSON or HTML depending on the Accept header — no branching in handler code.')
            ->withHighlights(['#[AsPayload(produces)]', 'Accept header', '?_format= override', 'ContentNegotiator'])
            ->withLearnMoreLabel('Toggle formats →')
            ->withDeepDiveLabel('How negotiation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/content-negotiation.html.twig', [
                'jsonPreview' => $jsonPreview,
            ]);
    }
}
