<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\LocalDomainPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;

#[AsPayloadHandler(payload: LocalDomainPayload::class, resource: DemoFeatureResource::class)]
final class LocalDomainHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(LocalDomainPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'get-started',
            'local-domain',
            'Local Domain',
            'Register `.test` domains through the built-in local-domain helper instead of relying on ad-hoc host setup.',
            ['TENANCY_BASE_DOMAIN', 'bin/semitexa local-domain:add', 'local-domain:list', 'server:restart'],
        );

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'local-domain',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('local-domain')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('A framework with tenancy should not be introduced through localhost forever. Register a stable local domain early and let the runtime behave like a product host.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the local domain flow →')
            ->withDeepDiveLabel('Why domain-first local work matters →')
            ->withResultPreviewTemplate($presentation->resultPreviewTemplate ?? '', $presentation->resultPreviewData)
            ->withL2ContentTemplate($presentation->l2ContentTemplate ?? '', $presentation->l2ContentData);
    }
}
