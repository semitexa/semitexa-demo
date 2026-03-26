<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\ParameterizedRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ParameterizedRoutePayload::class, resource: DemoFeatureResource::class)]
final class ParameterizedRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    private const PRODUCTS = [
        'headphones' => ['name' => 'Wireless Headphones', 'price' => '$79.99', 'category' => 'Audio'],
        'keyboard' => ['name' => 'Mechanical Keyboard', 'price' => '$129.99', 'category' => 'Input'],
        'monitor' => ['name' => 'Ultra-wide Monitor', 'price' => '$549.99', 'category' => 'Display'],
        'mouse' => ['name' => 'Ergonomic Mouse', 'price' => '$49.99', 'category' => 'Input'],
    ];

    public function handle(ParameterizedRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $slug = $payload->getSlug() ?: 'headphones';
        $product = self::PRODUCTS[$slug] ?? ['name' => 'Unknown Product', 'price' => 'N/A', 'category' => 'N/A'];
        $explanation = $this->explanationProvider->getExplanation('routing', 'parameterized') ?? [];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(ParameterizedRoutePayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        $resultPreview = '<div class="result-preview">'
            . '<p><strong>GET /demo/routing/product/' . htmlspecialchars($slug, ENT_QUOTES) . '</strong></p>'
            . '<p>Product: <strong>' . htmlspecialchars($product['name'], ENT_QUOTES) . '</strong></p>'
            . '<p>Price: ' . htmlspecialchars($product['price'], ENT_QUOTES) . '</p>'
            . '<p>Category: ' . htmlspecialchars($product['category'], ENT_QUOTES) . '</p>'
            . '<p class="result-preview__hint">Try: '
            . '<a href="/demo/routing/product/keyboard">keyboard</a> · '
            . '<a href="/demo/routing/product/monitor">monitor</a> · '
            . '<a href="/demo/routing/product/mouse">mouse</a>'
            . '</p>'
            . '</div>';

        return $resource
            ->pageTitle('Parameterized Route — Semitexa Demo')
            ->withSection('routing')
            ->withSlug('parameterized')
            ->withTitle('Parameterized Route')
            ->withSummary('Path parameters with regex constraints and typed injection.')
            ->withEntryLine('Path parameters like {slug} are extracted and injected via setters — with regex validation at the router level.')
            ->withHighlights(['requirements', 'defaults', 'RequestDtoHydrator', 'setter injection'])
            ->withLearnMoreLabel('Try different slugs →')
            ->withDeepDiveLabel('How regex compilation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreview($resultPreview);
    }
}
