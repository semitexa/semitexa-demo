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
        $groups = $meta['groups'] ?? [];

        $infoWhat = $meta['summary'];
        $infoHow = 'Each section groups working payloads that demonstrate one subsystem from entry-level view to implementation details.';
        $infoWhy = 'This page should act as a reliable launchpad, not a dump of disconnected examples.';

        if ($section === 'auth') {
            $infoWhat = 'Semitexa treats browser auth as a typed contract. We do not allow session state to dissolve into $this->session->get(\'current_user\') and other string-key guessing games.';
            $infoHow = 'If you want to persist auth state in the session, you declare a dedicated Session Payload and access it as a typed object. No magic array keys, no duplicated has/get checks, no hidden coupling between unrelated handlers.';
            $infoWhy = 'This closes one of the most common sources of legacy auth mess. Session state stays explicit, reviewable, and refactor-safe instead of turning into a bag of fragile string conventions.';
        } elseif ($section === 'get-started') {
            $infoWhat = 'Start Here is the onboarding path for people who need a trustworthy first boot, and it now points directly to the dedicated module structure page so the smallest HTTP spine is visible before the rest of the demo stack.';
            $infoHow = 'Read the first pass as a minimal HTTP stack: request contract, handler, response resource, template. Then expand into the full demo stack where catalog, feature tree, SEO defaults, grouped walkthroughs, and source-backed previews make the module feel product-like.';
            $infoWhy = 'If the first pages do not show responsibilities clearly, the demo still feels like a list of pages. This section should make the architecture legible before the reader dives into individual features.';
        } elseif ($section === 'llm') {
            $infoWhat = 'The `semitexa/llm` section documents the LLM module as a real subsystem: assistant command, skill declaration, manifest building, planning, providers, and guarded execution.';
            $infoHow = 'Read it in module order: first the assistant surface, then how commands become skills, then the execution flow, and finally the provider layer under the assistant.';
            $infoWhy = 'AI features are easy to oversell and hard to trust. This section keeps the package grounded in concrete framework contracts instead of vague product copy.';
        }

        $keywords = [$meta['label'], $meta['summary'], 'Semitexa Demo'];
        foreach ($features as $feature) {
            $keywords[] = $feature['title'];
        }

        return $resource
            ->pageTitle($meta['label'] . ' — Semitexa Demo')
            ->seoTagDefault('description', $meta['summary'])
            ->seoKeywords($keywords)
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getSidebarTree(),
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
            ->withGroups($groups)
            ->withFeatures($features);
    }
}
