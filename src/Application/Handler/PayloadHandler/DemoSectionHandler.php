<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Exception\NotFoundException;
use Semitexa\Demo\Application\Payload\Request\DemoSectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoSectionResource;
use Semitexa\Demo\Application\Service\DemoFeatureRegistry;

#[AsPayloadHandler(payload: DemoSectionPayload::class, resource: DemoSectionResource::class)]
final class DemoSectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeatureRegistry $featureRegistry;

    private const SECTION_META = [
        'routing' => [
            'label' => 'Routing & Handlers',
            'summary' => 'Attribute-driven routes, typed handlers, content negotiation.',
            'icon' => '🔀',
        ],
        'di' => [
            'label' => 'Dependency Injection',
            'summary' => 'Two-tier DI, service contracts, scoped injection for Swoole.',
            'icon' => '🔌',
        ],
        'data' => [
            'label' => 'Data & Persistence',
            'summary' => 'Attribute-driven ORM, query builder, relations, pagination.',
            'icon' => '💾',
        ],
        'auth' => [
            'label' => 'Auth & Security',
            'summary' => 'Session auth, machine tokens, RBAC, protected routes.',
            'icon' => '🔐',
        ],
        'events' => [
            'label' => 'Events & Async',
            'summary' => 'Sync/async events, deferred execution, queued handlers.',
            'icon' => '⚡',
        ],
        'rendering' => [
            'label' => 'Rendering & SSR',
            'summary' => 'Twig templates, slot resources, deferred blocks, reactive updates.',
            'icon' => '🎨',
        ],
        'platform' => [
            'label' => 'Platform',
            'summary' => 'Multi-tenancy, i18n, search, caching, mail, scheduler, workflow.',
            'icon' => '🏗️',
        ],
        'api' => [
            'label' => 'Intelligent API',
            'summary' => 'External API endpoints, versioning, consumer profiles, machine auth.',
            'icon' => '🌐',
        ],
        'testing' => [
            'label' => 'Testing & CLI',
            'summary' => 'Automated contract testing, payload strategies, CLI tools.',
            'icon' => '🧪',
        ],
    ];

    public function handle(DemoSectionPayload $payload, DemoSectionResource $resource): DemoSectionResource
    {
        $section = $payload->getSection();
        $meta = self::SECTION_META[$section] ?? null;

        if ($meta === null) {
            throw new NotFoundException('Demo section', $section);
        }

        $features = $this->featureRegistry->getBySection($section);

        return $resource
            ->pageTitle($meta['label'] . ' — Semitexa Demo')
            ->withSection($section)
            ->withSectionLabel($meta['label'])
            ->withSectionIcon($meta['icon'])
            ->withSectionSummary($meta['summary'])
            ->withFeatures($features);
    }
}
