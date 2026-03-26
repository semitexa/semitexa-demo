<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Exception\NotFoundException;
use Semitexa\Demo\Application\Payload\Request\DemoSectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoSectionResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: DemoSectionPayload::class, resource: DemoSectionResource::class)]
final class DemoSectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DemoSectionPayload $payload, DemoSectionResource $resource): DemoSectionResource
    {
        $section = $payload->getSection();
        $meta = $this->catalog->getSection($section);

        if ($meta === null) {
            throw new NotFoundException('Demo section', $section);
        }

        $features = array_map(
            static fn (array $feature): array => [
                'slug' => $feature['slug'],
                'title' => $feature['title'],
                'summary' => $feature['summary'],
                'href' => $feature['href'],
            ],
            $meta['features'],
        );

        return $resource
            ->pageTitle($meta['label'] . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => $section,
                'currentSlug' => null,
                'infoWhat' => $meta['summary'],
                'infoHow' => 'Each section groups working payloads that demonstrate one subsystem from entry-level view to implementation details.',
                'infoWhy' => 'This page should act as a reliable launchpad, not a dump of disconnected examples.',
                'infoKeywords' => [],
            ])
            ->withSection($section)
            ->withSectionLabel($meta['label'])
            ->withSectionIcon($meta['icon'])
            ->withSectionSummary($meta['summary'])
            ->withFeatures($features);
    }
}
