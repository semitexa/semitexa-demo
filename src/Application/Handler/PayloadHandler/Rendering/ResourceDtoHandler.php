<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\ResourceDtoPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsPayloadHandler(payload: ResourceDtoPayload::class, resource: DemoFeatureResource::class)]
final class ResourceDtoHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ResourceDtoPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('rendering', 'resource-dtos') ?? [];

        return $resource
            ->pageTitle('Resource DTOs — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'resource-dtos',
                'infoWhat' => $explanation['what'] ?? 'A Resource DTO is the typed presentation boundary between handler code and templates.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('resource-dtos')
            ->withTitle('Resource DTOs')
            ->withSummary('A Resource DTO is the one typed source of presentation data: handlers shape it once, templates consume it everywhere, and no view has to dissect random arrays.')
            ->withEntryLine('Real separation means templates receive one explicit response object, not loose arrays and last-minute data surgery.')
            ->withHighlights(['#[AsResource]', 'HtmlResponse', 'with*() methods', 'typed view data', 'auto render'])
            ->withLearnMoreLabel('See the response boundary →')
            ->withDeepDiveLabel('How the resource pipeline works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/resource-dto-showcase.html.twig', [
                'painPoints' => [
                    'When templates receive loose arrays, presentation rules leak into Twig and data preparation gets scattered.',
                    'Different partials start reshaping the same data in different ways because there is no single canonical response object.',
                    'The handler stops looking like application logic and starts becoming an ad hoc template assembler.',
                ],
                'signals' => [
                    ['value' => '1', 'label' => 'canonical presentation object'],
                    ['value' => '0', 'label' => 'template-side data surgery'],
                    ['value' => '100%', 'label' => 'typed handoff to the view layer'],
                ],
                'compare' => [
                    [
                        'variant' => 'warning',
                        'eyebrow' => 'Blurry Presentation Boundary',
                        'title' => 'Arrays drift through handlers and templates',
                        'summary' => 'Data arrives as loose arrays, templates reshape it again, and every partial carries hidden mapping rules.',
                        'note' => 'The real response contract is nowhere explicit, so presentation logic spreads across the stack.',
                    ],
                    [
                        'variant' => 'active',
                        'eyebrow' => 'Single Source Of View Data',
                        'title' => 'Resource DTO drives the page',
                        'summary' => 'The handler populates one typed response object through explicit with*() methods, and Twig only renders what the resource already decided.',
                        'note' => 'Data shaping happens once, in one place, with names the whole presentation layer can trust.',
                    ],
                ],
                'columns' => [
                    ['name' => 'productName', 'owner' => 'Resource DTO', 'note' => 'Normalized once for headings, badges, and links.'],
                    ['name' => 'priceLabel', 'owner' => 'Resource DTO', 'note' => 'Formatting decision lives before Twig, not inside partials.'],
                    ['name' => 'inventoryState', 'owner' => 'Resource DTO', 'note' => 'Template consumes one semantic field, not branching raw numbers.'],
                    ['name' => 'heroActions', 'owner' => 'Resource DTO', 'note' => 'All CTA metadata is explicit and reusable across page regions.'],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/resource-dto-rules.html.twig', [
                'rules' => [
                    'The Resource DTO is the response contract for templates, not a passive bucket for whatever data happened to be nearby.',
                    'Handlers should populate presentation fields deliberately through named with*() methods.',
                    'Twig should render data, not reinterpret domain state or normalize arrays on the fly.',
                    'Once the resource is complete, auto-rendering can stay mechanical and reliable.',
                ],
                'checks' => [
                    ['label' => '#[AsResource]', 'detail' => 'Declares the template and render handle directly on the response DTO.'],
                    ['label' => 'with*() methods', 'detail' => 'Create one explicit vocabulary for everything the template is allowed to consume.'],
                    ['label' => 'HtmlResponse', 'detail' => 'Provides render context accumulation and automatic template rendering after the handler pipeline.'],
                ],
            ])
            ->withSourceCode([
                'Array Drift Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Rendering/ResourceDto/LegacyProductPageHandler.example.php'),
                'Resource DTO' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Rendering/ResourceDto/ProductShowcaseResource.example.php'),
                'Resource Handler' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Rendering/ResourceDto/ProductShowcaseHandler.example.php'),
                'HtmlResponse' => $this->sourceCodeReader->readClassSource(HtmlResponse::class),
                'DemoFeatureResource' => $this->sourceCodeReader->readClassSource(DemoFeatureResource::class),
                'Feature Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withExplanation($explanation);
    }
}
