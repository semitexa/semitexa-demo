<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

/**
 * The "Semitexa in 3 minutes" series, in watching order.
 *
 * One entry per published video on the @semitexa channel. A new video is added
 * by appending an entry here; nothing else changes. `docs` points at the demo
 * page that covers the same topic with live output and source.
 */
#[AsService]
final class DemoVideoCatalog
{
    /**
     * @var list<array{id: string, page: string|null, title: string, summary: string, duration: string, docsHref: string, docsLabel: string}>
     */
    private const VIDEOS = [
        [
            'id' => 'ZiESds3mUmw',
            'page' => null,
            'title' => 'Get started with Semitexa',
            'summary' => 'Install with one command, run on real local domains, serve several tenants from one app, and stream a slow block into a page. The Docker build and restart are sped up, and the 1.5 s report query is simulated.',
            'duration' => '4:13',
            'docsHref' => '/demo/get-started/installation',
            'docsLabel' => 'Installation',
        ],
        [
            'id' => 'tZxIdhwlTP8',
            'page' => 'routing/basic',
            'title' => 'A page from one attribute',
            'summary' => 'Routing without config files: one payload attribute makes a route, and a path parameter reaches the handler.',
            'duration' => '2:44',
            'docsHref' => '/demo/routing/basic',
            'docsLabel' => 'Routing',
        ],
        [
            'id' => 'Z1o3bI_N9JU',
            'page' => 'routing/payload-shield',
            'title' => 'The payload is a shield',
            'summary' => 'A classic controller with six jobs next to payload, handler and resource. Invalid input gets a 422 before the handler runs.',
            'duration' => '3:59',
            'docsHref' => '/demo/routing/payload-shield',
            'docsLabel' => 'Payload shield',
        ],
        [
            'id' => 'CtX0Ik6KCnM',
            'page' => 'rendering/slots',
            'title' => 'Pages, slots and components',
            'summary' => 'A page as a tree of resources: a layout with slots, and a component with its own state that reacts to clicks without a page reload.',
            'duration' => '2:56',
            'docsHref' => '/demo/rendering/slots',
            'docsLabel' => 'Slots',
        ],
        [
            'id' => 'FmxITKQKDDU',
            'page' => 'rendering/deferred-live',
            'title' => 'Live pages with server-sent events',
            'summary' => 'A deferred slot that refreshes itself from the server: one document, one event stream, no polling requests and no WebSocket server.',
            'duration' => '2:57',
            'docsHref' => '/demo/rendering/deferred-live',
            'docsLabel' => 'Deferred live',
        ],
        [
            'id' => '-O20S376R0A',
            'page' => 'data/domain-models',
            'title' => 'Why another ORM? Built for Swoole',
            'summary' => 'In a long-lived worker with many concurrent coroutines, state can leak between requests and tenants. Measured isolation, memory growth and fail-closed tenancy.',
            'duration' => '3:53',
            'docsHref' => '/demo/data/domain-models',
            'docsLabel' => 'Domain models',
        ],
        [
            'id' => 'OXIB8LyykUI',
            'page' => 'di/overview',
            'title' => 'Dependency injection in a long-lived worker: what goes wrong',
            'summary' => 'A shared counter that climbs per worker, and request data in a shared service that answers with another tenant. The problem, measured on a live stand.',
            'duration' => '2:42',
            'docsHref' => '/demo/di/overview',
            'docsLabel' => 'DI overview',
        ],
        [
            'id' => 'dZWkGZPrR0Y',
            'page' => 'di/overview',
            'title' => 'Dependency injection by lifetime',
            'summary' => 'Every injected object has a lifetime written at the injection point, and the wiring is checked at boot. Includes the honest limit of what is checked.',
            'duration' => '3:18',
            'docsHref' => '/demo/di/contracts',
            'docsLabel' => 'Contracts',
        ],
        [
            'id' => 'HHsP0FTdRMA',
            'page' => 'auth/protected',
            'title' => 'Who may call this route',
            'summary' => 'Sign-in, a protected route, a required permission, and the difference between 401 and 403.',
            'duration' => '2:47',
            'docsHref' => '/demo/auth/protected',
            'docsLabel' => 'Protected routes',
        ],
        [
            'id' => 'LAq5DTZCQlo',
            'page' => 'events/sync',
            'title' => 'Events and jobs: why are there two systems?',
            'summary' => 'Most stacks keep events and background jobs apart. A look at why, and at what one mechanism would change.',
            'duration' => '2:21',
            'docsHref' => '/demo/events/sync',
            'docsLabel' => 'Events',
        ],
    ];

    /**
     * Videos shown on one demo page, addressed as `section/slug`.
     *
     * @return list<array{id: string, page: string|null, title: string, summary: string, duration: string, docsHref: string, docsLabel: string, watchUrl: string, thumbUrl: string, embedUrl: string, number: int}>
     */
    public function forPage(string $section, string $slug): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (array $video): bool => $video['page'] === $section . '/' . $slug,
        ));
    }

    /**
     * @return list<array{id: string, page: string|null, title: string, summary: string, duration: string, docsHref: string, docsLabel: string, watchUrl: string, thumbUrl: string, embedUrl: string, number: int}>
     */
    public function all(): array
    {
        $videos = [];
        foreach (self::VIDEOS as $index => $video) {
            $videos[] = $video + [
                'number' => $index,
                'watchUrl' => 'https://www.youtube.com/watch?v=' . $video['id'],
                'thumbUrl' => 'https://i.ytimg.com/vi/' . $video['id'] . '/hqdefault.jpg',
                'embedUrl' => 'https://www.youtube.com/embed/' . $video['id'] . '?autoplay=1&rel=0',
            ];
        }

        return $videos;
    }
}
