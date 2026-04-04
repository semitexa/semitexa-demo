<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\ContentNegotiationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ContentNegotiationPayload::class, resource: DemoFeatureResource::class)]
final class ContentNegotiationHandler implements TypedHandlerInterface
{
    private const FEATURE_TITLE = 'Content Negotiation';
    private const FEATURE_SUMMARY = 'One endpoint, multiple response formats — automatically.';
    private const FEATURE_ENTRY_LINE = 'One endpoint serves JSON or HTML depending on the Accept header — no branching in handler code.';
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
        $shellContext = $this->buildShellContext($explanation);

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(ContentNegotiationPayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        $jsonPreview = json_encode(['products' => self::PRODUCTS], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}';

        $keywords = ['routing', 'content negotiation', 'html', 'json', 'payload'];

        return $resource
            ->pageTitle(self::FEATURE_TITLE . ' — Semitexa Demo')
            ->seoTag('description', self::FEATURE_ENTRY_LINE)
            ->seoTag('keywords', implode(', ', $keywords))
            ->seoTag('og:title', self::FEATURE_TITLE . ' — Semitexa Demo')
            ->seoTag('og:description', self::FEATURE_ENTRY_LINE)
            ->seoTag('og:type', 'article')
            ->withDemoShellContext($shellContext)
            ->withSection('routing')
            ->withSlug('content-negotiation')
            ->withTitle(self::FEATURE_TITLE)
            ->withSummary(self::FEATURE_SUMMARY)
            ->withEntryLine(self::FEATURE_ENTRY_LINE)
            ->withHighlights(['#[AsPayload(produces)]', 'Accept header', '?_format= override', 'ContentNegotiator'])
            ->withLearnMoreLabel('Toggle formats →')
            ->withDeepDiveLabel('How negotiation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/content-negotiation.html.twig', [
                'jsonPreview' => $jsonPreview,
                'products' => self::PRODUCTS,
            ]);
    }

    private function buildShellContext(array $explanation): array
    {
        return [
            'navSections' => $this->catalog->getSections(),
            'featureTree' => $this->catalog->getFeatureTree(),
            'currentSection' => 'routing',
            'currentSlug' => 'content-negotiation',
            'infoWhat' => $explanation['what'] ?? self::FEATURE_SUMMARY,
            'infoHow' => $explanation['how'] ?? null,
            'infoWhy' => $explanation['why'] ?? null,
            'infoKeywords' => $explanation['keywords'] ?? [],
        ];
    }
}
