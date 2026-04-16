<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\BaseTenantPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;

#[AsPayloadHandler(payload: BaseTenantPayload::class, resource: DemoFeatureResource::class)]
final class BaseTenantHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(BaseTenantPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'get-started',
            'base-tenant',
            'Base Tenant',
            'Define the first tenant through environment variables and resolve it through a real local host.',
            ['tenant', 'tenant context', 'tenant config', 'default tenant'],
        );

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'base-tenant',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('base-tenant')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('The first tenant is configuration, not ceremony: define it in `.env`, register the host, restart, and open the tenant like a real product surface.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the tenant bootstrap flow →')
            ->withDeepDiveLabel('How Semitexa resolves that tenant →')
            ->withResultPreviewTemplate($presentation->resultPreviewTemplate ?? '', $presentation->resultPreviewData)
            ->withL2ContentTemplate($presentation->l2ContentTemplate ?? '', $presentation->l2ContentData);
    }
}
