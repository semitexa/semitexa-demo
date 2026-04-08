<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\ResourceDtoPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

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
                'flow' => [
                    ['step' => '1', 'title' => 'Handler receives a typed resource', 'detail' => 'The payload declares `responseWith`, so the handler gets a concrete Resource DTO instead of assembling loose arrays.'],
                    ['step' => '2', 'title' => 'Handler fills named fields', 'detail' => 'Presentation data is pushed through explicit `with*()` methods before Twig sees anything.'],
                    ['step' => '3', 'title' => 'Twig renders the finished contract', 'detail' => 'The template reads one stable response object instead of reformatting raw business data on its own.'],
                ],
                'fields' => [
                    ['name' => 'title', 'detail' => 'Page heading already shaped for the template.'],
                    ['name' => 'summary', 'detail' => 'Intro copy prepared in the handler, not reconstructed in Twig.'],
                    ['name' => 'highlights', 'detail' => 'Structured view data ready for repeated rendering blocks.'],
                    ['name' => 'resultPreviewData', 'detail' => 'Nested preview state passed as one explicit resource field.'],
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
                'Resource DTO' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Rendering/ResourceDto/ProductShowcaseResource.example.php'),
                'Handler -> Resource DTO' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Rendering/ResourceDto/ProductShowcaseHandler.example.php'),
            ])
            ->withExplanation($explanation);
    }
}
