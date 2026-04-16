<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\ModuleStructurePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;

#[AsPayloadHandler(payload: ModuleStructurePayload::class, resource: DemoFeatureResource::class)]
final class ModuleStructureHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(ModuleStructurePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'get-started',
            'module-structure',
            'Module Structure',
            'The minimal Semitexa module is a typed HTTP spine: payload, handler, resource, and template. The full demo stack adds catalog, shell, and SEO layers on top.',
            ['Payload', 'Handler', 'Resource', 'Template', 'Catalog', 'SEO'],
        );

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getSidebarTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'module-structure',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('module-structure')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Start with the smallest useful module shape, then expand the system around it instead of hiding the request path under the product shell.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the minimal stack →')
            ->withDeepDiveLabel('See the full module map →')
            ->withRelatedPayloads([
                ['href' => '/demo/get-started/installation', 'label' => 'Installation'],
                ['href' => '/demo/get-started/beyond-controllers', 'label' => 'Beyond Controllers'],
            ])
            ->withResultPreviewTemplate($presentation->resultPreviewTemplate ?? '', $presentation->resultPreviewData);
    }
}
