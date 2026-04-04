<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
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
                'opensInNewTab' => $feature['opensInNewTab'] ?? false,
                'href' => $feature['href'],
            ],
            $meta['features'],
        );

        $infoWhat = $meta['summary'];
        $infoHow = 'Each section groups working payloads that demonstrate one subsystem from entry-level view to implementation details.';
        $infoWhy = 'This page should act as a reliable launchpad, not a dump of disconnected examples.';

        if ($section === 'auth') {
            $infoWhat = 'Semitexa treats browser auth as a typed contract. We do not allow session state to dissolve into $this->session->get(\'current_user\') and other string-key guessing games.';
            $infoHow = 'If you want to persist auth state in the session, you declare a dedicated Session Payload and access it as a typed object. No magic array keys, no duplicated has/get checks, no hidden coupling between unrelated handlers.';
            $infoWhy = 'This closes one of the most common sources of legacy auth mess. Session state stays explicit, reviewable, and refactor-safe instead of turning into a bag of fragile string conventions.';
        } elseif ($section === 'get-started') {
            $infoWhat = 'Get Started is the onboarding path for people who need a trustworthy first boot, not a tour of disconnected features. The sequence should move from installation to a real local host and then to the first tenant boundary.';
            $infoHow = 'Start with Installation so the runtime is up and inspectable. Then use Local Domain and Base Tenant to promote localhost into a product-like host setup before you branch into deeper framework concepts.';
            $infoWhy = 'If the first pages do not form a coherent path, the demo reads like a catalogue instead of an onboarding story. This section should reduce ambiguity in the first hour.';
        }

        return $resource
            ->pageTitle($meta['label'] . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => $section,
                'currentSlug' => null,
                'infoWhat' => $infoWhat,
                'infoHow' => $infoHow,
                'infoWhy' => $infoWhy,
                'infoKeywords' => [],
            ])
            ->withSection($section)
            ->withSectionLabel($meta['label'])
            ->withSectionIcon($meta['icon'])
            ->withSectionSummary($meta['summary'])
            ->withFeatures($features);
    }
}
