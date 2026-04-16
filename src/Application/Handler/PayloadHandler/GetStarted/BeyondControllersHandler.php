<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\BeyondControllersPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: BeyondControllersPayload::class, resource: DemoFeatureResource::class)]
final class BeyondControllersHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(BeyondControllersPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'get-started',
            'beyond-controllers',
            'Beyond Controllers',
            'Controller-first design bundles too many responsibilities into one unstable class. Semitexa splits the transport contract, the use case, and the response shape deliberately.',
            ['Payload owns slug contract', 'Handler owns use case', 'Resource owns response shape', 'No controller glue'],
        );

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/GetStarted/ProductShowcasePayload.example.php',
            ),
            'Handler' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/GetStarted/ProductShowcaseHandler.example.php',
            ),
            'Resource' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/GetStarted/ProductShowcaseResource.example.php',
            ),
            'Legacy Controller Example' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/GetStarted/LegacyProductShowController.example.php',
            ),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'beyond-controllers',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('beyond-controllers')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('If one class owns the route, request parsing, validation, auth assumptions, business orchestration, and response assembly, it stops being simple and starts being the hidden coupling point of the whole feature.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See why controllers stop scaling →')
            ->withDeepDiveLabel('How the Semitexa split stays reviewable →')
            ->withResultPreviewTemplate($presentation->resultPreviewTemplate ?? '', $presentation->resultPreviewData)
            ->withL2ContentTemplate($presentation->l2ContentTemplate ?? '', $presentation->l2ContentData)
            ->withL3ContentTemplate($presentation->l3ContentTemplate ?? '', $presentation->l3ContentData)
            ->withSourceCode($sourceCode);
    }
}
