<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\LocaleSetupPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;

#[AsPayloadHandler(payload: LocaleSetupPayload::class, resource: DemoFeatureResource::class)]
final class LocaleSetupHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(LocaleSetupPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'get-started',
            'locale-setup',
            'Locale Setup',
            'Configure the minimal Semitexa Locale contract: default locale, supported locales, JSON catalogs, and one Twig translation check.',
            ['LOCALE_DEFAULT', 'LOCALE_SUPPORTED', 'Application/View/locales/{lang}.json', 'trans()'],
        );

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'locale-setup',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('locale-setup')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Locale should become a boring, explicit part of project setup: pick the default, declare supported locales, add JSON catalogs, and verify one translated string end to end.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the minimal locale setup →')
            ->withDeepDiveLabel('What to verify before adding more languages →')
            ->withResultPreviewTemplate($presentation->resultPreviewTemplate ?? '', $presentation->resultPreviewData)
            ->withL2ContentTemplate($presentation->l2ContentTemplate ?? '', $presentation->l2ContentData);
    }
}
