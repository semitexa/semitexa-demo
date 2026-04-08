<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\ModuleStructurePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: ModuleStructurePayload::class, resource: DemoFeatureResource::class)]
final class ModuleStructureHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ModuleStructurePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'A Semitexa module begins with one minimal HTTP spine: payload, handler, resource, and template. Everything else is an extension of that path, not a replacement for it.',
            'how' => 'The payload owns the route contract and inbound data boundary. The handler owns the use case. The resource owns the outgoing response and SEO metadata. The template owns presentation only. That keeps the request path coherent before the larger demo shell is added on top.',
            'why' => 'First-time visitors should be able to explain a module in one sentence before they learn the whole catalog. A small, typed spine keeps the architecture legible, while the full catalog can still grow around it without changing the contract of the request itself.',
            'keywords' => [
                ['term' => 'Payload', 'definition' => 'Owns the HTTP contract, request boundary, and route metadata.'],
                ['term' => 'Handler', 'definition' => 'Owns the use case and orchestration for one payload/resource pair.'],
                ['term' => 'Resource', 'definition' => 'Owns the response shape, metadata, and render context.'],
                ['term' => 'Template', 'definition' => 'Owns presentation only and stays declarative.'],
                ['term' => 'Catalog', 'definition' => 'Groups live routes into the guided Start Here path and the full route-first map.'],
                ['term' => 'SEO', 'definition' => 'Valid title and description metadata are part of the demo shell, not an afterthought.'],
            ],
        ];

        return $resource
            ->pageTitle('Module Structure — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getSidebarTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'module-structure',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('module-structure')
            ->withTitle('Module Structure')
            ->withSummary('The minimal Semitexa module is a typed HTTP spine: payload, handler, resource, and template. The full demo stack adds catalog, shell, and SEO layers on top.')
            ->withEntryLine('Start with the smallest useful module shape, then expand the system around it instead of hiding the request path under the product shell.')
            ->withHighlights(['Payload', 'Handler', 'Resource', 'Template', 'Catalog', 'SEO'])
            ->withLearnMoreLabel('See the minimal stack →')
            ->withDeepDiveLabel('See the full module map →')
            ->withRelatedPayloads([
                ['href' => '/demo/get-started/installation', 'label' => 'Installation'],
                ['href' => '/demo/get-started/beyond-controllers', 'label' => 'Beyond Controllers'],
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/module-structure-files.html.twig', [
                'title' => 'File tree: a contact form module example',
                'summary' => 'Each file points to the page that explains that concern in more detail.',
                'tree' => <<<'TREE'
packages/semitexa-demo/src/Application/
├── Payload/
│   └── Request/
│       └── <a href="/demo/routing/payload-shield">ContactFormPayload.php</a>
├── Handler/
│   └── PayloadHandler/
│       └── <a href="/demo/get-started/beyond-controllers">ContactFormHandler.php</a>
├── Resource/
│   └── Response/
│       └── <a href="/demo/rendering/resource-dtos">ContactFormResource.php</a>
└── View/
    └── templates/
        └── pages/
            └── <a href="/demo/rendering/resource-dtos">contact-form.html.twig</a>
TREE
            ]);
    }
}
