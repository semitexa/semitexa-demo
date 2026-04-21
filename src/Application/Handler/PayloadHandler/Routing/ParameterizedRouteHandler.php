<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Routing\ParameterizedRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ParameterizedRoutePayload::class, resource: DemoFeatureResource::class)]
final class ParameterizedRouteHandler implements TypedHandlerInterface
{
    private const PRODUCTS = [
        'headphones' => ['name' => 'Wireless Headphones', 'price' => '$79.99', 'category' => 'Audio'],
        'keyboard' => ['name' => 'Mechanical Keyboard', 'price' => '$129.99', 'category' => 'Input'],
        'monitor' => ['name' => 'Ultra-wide Monitor', 'price' => '$549.99', 'category' => 'Display'],
        'mouse' => ['name' => 'Ergonomic Mouse', 'price' => '$49.99', 'category' => 'Input'],
    ];

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ParameterizedRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $slug = $payload->getSlug() ?: 'headphones';
        $product = self::PRODUCTS[$slug] ?? ['name' => 'Unknown Product', 'price' => 'N/A', 'category' => 'N/A'];

        $spec = new FeatureSpec(
            section: 'routing',
            slug: 'parameterized',
            entryLine: 'Path parameters like {slug} are extracted and injected via setters — with regex validation at the router level.',
            learnMoreLabel: 'Try different slugs →',
            deepDiveLabel: 'How regex compilation works →',
            relatedSlugs: [],
            fallbackTitle: 'Parameterized Route',
            fallbackSummary: 'Path parameters with regex constraints and typed injection.',
            fallbackHighlights: ['requirements', 'defaults', 'PayloadHydrator', 'setter injection'],
            explanation: $this->explanationProvider->getExplanation('routing', 'parameterized') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Payload' => $this->sourceCodeReader->readClassSource(ParameterizedRoutePayload::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/product-spotlight.html.twig', [
                'slug' => $slug,
                'product' => $product,
                'examples' => [
                    ['label' => 'keyboard', 'href' => '/demo/routing/parameterized/keyboard'],
                    ['label' => 'monitor', 'href' => '/demo/routing/parameterized/monitor'],
                    ['label' => 'mouse', 'href' => '/demo/routing/parameterized/mouse'],
                ],
            ]);
    }
}
