<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\VideosPayload;
use Semitexa\Demo\Application\Resource\Response\DemoSectionResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoVideoCatalog;

#[AsPayloadHandler(payload: VideosPayload::class, resource: DemoSectionResource::class)]
final class VideosHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoVideoCatalog $videos;

    public function handle(VideosPayload $payload, DemoSectionResource $resource): DemoSectionResource
    {
        $summary = 'Short videos filmed on a real project, one topic each: routing, the payload shield, rendering, live pages, data, dependency injection, auth and events.';

        return $resource
            ->pageTitle('Videos | Semitexa Demo')
            ->seoTagDefault('description', $summary)
            ->seoKeywords(['Semitexa videos', 'Semitexa in 3 minutes', 'Semitexa tutorial', 'Semitexa Demo'])
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getSidebarTree(),
                'currentSection' => 'videos',
                'currentSlug' => null,
                'infoWhat' => 'The "Semitexa in 3 minutes" series: each video shows one idea working on a real project, with the code on screen.',
                'infoHow' => 'Watch in order, or jump to the topic you need. Each video links to the demo page that covers the same topic with live output and source.',
                'infoWhy' => 'A short film is often the fastest way to see how the parts fit before reading the details.',
                'infoKeywords' => [],
            ])
            ->withSection('videos')
            ->withSectionLabel('Videos')
            ->withSectionIcon('▶')
            ->withSectionSummary($summary)
            ->withGroups([])
            ->withFeatures([])
            ->with('videos', $this->videos->all());
    }
}
