<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Routing\ContentNegotiationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ContentNegotiationPayload::class, resource: DemoFeatureResource::class)]
final class ContentNegotiationHandler implements TypedHandlerInterface
{
    private const ENTRY_LINE = 'One endpoint serves JSON or HTML depending on the Accept header — no branching in handler code.';

    private const PRODUCTS = [
        ['id' => '1', 'name' => 'Wireless Headphones', 'price' => 79.99],
        ['id' => '2', 'name' => 'Mechanical Keyboard', 'price' => 129.99],
        ['id' => '3', 'name' => 'Ultra-wide Monitor', 'price' => 549.99],
    ];

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ContentNegotiationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'routing',
            slug: 'content-negotiation',
            entryLine: self::ENTRY_LINE,
            learnMoreLabel: 'Toggle formats →',
            deepDiveLabel: 'How negotiation works →',
            relatedSlugs: [],
            fallbackTitle: 'Content Negotiation',
            fallbackSummary: 'One endpoint, multiple response formats — automatically.',
            fallbackHighlights: ['#[AsPayload(produces)]', 'Accept header', '?_format= override', 'ContentNegotiator'],
            explanation: $this->explanationProvider->getExplanation('routing', 'content-negotiation') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $pageTitle = $this->projector->describe($spec)->pageTitle();

        return $this->projector->project($resource, $spec)
            ->seoTag('og:title', $pageTitle)
            ->seoTag('og:description', self::ENTRY_LINE)
            ->seoTag('og:type', 'article')
            ->withSourceCode([
                'Payload' => $this->sourceCodeReader->readClassSource(ContentNegotiationPayload::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/content-negotiation.html.twig', [
                'jsonPreview' => json_encode(['products' => self::PRODUCTS], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}',
                'products' => self::PRODUCTS,
            ]);
    }
}
