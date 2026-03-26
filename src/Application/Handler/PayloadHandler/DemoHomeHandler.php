<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\DemoHomePayload;
use Semitexa\Demo\Application\Resource\Response\DemoHomeResource;
use Semitexa\Demo\Application\Service\DemoFeatureRegistry;

#[AsPayloadHandler(payload: DemoHomePayload::class, resource: DemoHomeResource::class)]
final class DemoHomeHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeatureRegistry $featureRegistry;

    private const SECTION_META = [
        'routing' => [
            'key' => 'routing',
            'label' => 'Routing & Handlers',
            'summary' => 'Attribute-driven routes, typed handlers, content negotiation.',
            'icon' => '🔀',
            'starter' => true,
            'prerequisites' => [],
        ],
        'di' => [
            'key' => 'di',
            'label' => 'Dependency Injection',
            'summary' => 'Two-tier DI, service contracts, scoped injection for Swoole.',
            'icon' => '🔌',
            'starter' => true,
            'prerequisites' => [],
        ],
        'data' => [
            'key' => 'data',
            'label' => 'Data & Persistence',
            'summary' => 'Attribute-driven ORM, query builder, relations, pagination.',
            'icon' => '💾',
            'starter' => true,
            'prerequisites' => [],
        ],
        'auth' => [
            'key' => 'auth',
            'label' => 'Auth & Security',
            'summary' => 'Session auth, machine tokens, RBAC, protected routes.',
            'icon' => '🔐',
            'starter' => false,
            'prerequisites' => [],
        ],
        'events' => [
            'key' => 'events',
            'label' => 'Events & Async',
            'summary' => 'Sync/async events, deferred execution, queued handlers.',
            'icon' => '⚡',
            'starter' => false,
            'prerequisites' => [],
        ],
        'rendering' => [
            'key' => 'rendering',
            'label' => 'Rendering & SSR',
            'summary' => 'Twig templates, slot resources, deferred blocks, reactive updates.',
            'icon' => '🎨',
            'starter' => false,
            'prerequisites' => [],
        ],
        'platform' => [
            'key' => 'platform',
            'label' => 'Platform',
            'summary' => 'Multi-tenancy, i18n, search, caching, mail, scheduler, workflow.',
            'icon' => '🏗️',
            'starter' => false,
            'prerequisites' => ['data', 'rendering'],
        ],
        'api' => [
            'key' => 'api',
            'label' => 'Intelligent API',
            'summary' => 'External API endpoints, versioning, consumer profiles, machine auth.',
            'icon' => '🌐',
            'starter' => false,
            'prerequisites' => ['routing', 'auth'],
        ],
        'testing' => [
            'key' => 'testing',
            'label' => 'Testing & CLI',
            'summary' => 'Automated contract testing, payload strategies, CLI tools.',
            'icon' => '🧪',
            'starter' => false,
            'prerequisites' => ['routing'],
        ],
    ];

    public function handle(DemoHomePayload $payload, DemoHomeResource $resource): DemoHomeResource
    {
        $sections = [];
        $starterSections = [];
        $totalFeatureCount = 0;

        foreach (self::SECTION_META as $key => $meta) {
            $features = $this->featureRegistry->getBySection($key);
            $featureCount = count($features);
            $totalFeatureCount += $featureCount;

            $section = array_merge($meta, [
                'featureCount' => $featureCount,
            ]);

            $sections[] = $section;

            if ($meta['starter']) {
                $starterSections[] = $section;
            }
        }

        return $resource
            ->pageTitle('Semitexa Demo — Build faster. Ship safer. Scale effortlessly.')
            ->withSections($sections)
            ->withStarterSections($starterSections)
            ->withTotalFeatureCount($totalFeatureCount);
    }
}
