<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\DemoHomePayload;
use Semitexa\Demo\Application\Resource\Response\DemoHomeResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: DemoHomePayload::class, resource: DemoHomeResource::class)]
final class DemoHomeHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DemoHomePayload $payload, DemoHomeResource $resource): DemoHomeResource
    {
        $sections = $this->catalog->getSections();
        $starterSections = $this->catalog->getStarterSections();
        $featuredFeatures = $this->catalog->getFeaturedFeatures();
        $homeCatalog = [
            'sections' => $sections,
            'starterSections' => $starterSections,
            'featuredFeatures' => $featuredFeatures,
            'totalFeatureCount' => $this->catalog->getTotalFeatureCount(),
        ];

        return $resource
            ->pageTitle('Semitexa Demo — Build faster. Ship safer. Scale effortlessly.')
            ->withDemoShellContext([
                'navSections' => $sections,
                'featureTree' => $sections,
                'currentSection' => null,
                'currentSlug' => null,
                'infoWhat' => 'Production-like walkthroughs for the Semitexa runtime, not disconnected toy snippets.',
                'infoHow' => 'Start from the shell, open a section, then drill into feature pages with live previews and source.',
                'infoWhy' => 'A demo package should prove that the framework feels coherent before anyone reads the docs.',
                'infoKeywords' => [],
            ])
            ->withRelease([
                'label' => 'First release',
                'date' => '22 April 2026',
                'target' => '2026-04-22T00:00:00+03:00',
            ])
            ->withHomeCatalog($homeCatalog);
    }
}
