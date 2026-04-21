<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Environment;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Routing\EnvRouteOverridePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: EnvRouteOverridePayload::class, resource: DemoFeatureResource::class)]
final class EnvRouteOverrideHandler implements TypedHandlerInterface
{
    private const ENV_KEY = 'DEMO_ENV_ROUTE_OVERRIDE_PATH';
    private const FALLBACK_PATH = '/demo/routing/env-route-override';

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(EnvRouteOverridePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'routing',
            slug: 'env-route-override',
            entryLine: 'The route still lives in PHP, but deployment can move the public URL without reopening the payload class.',
            learnMoreLabel: 'See env override pattern →',
            deepDiveLabel: 'How resolved route metadata works →',
            relatedSlugs: [],
            fallbackTitle: 'Env Route Override',
            fallbackSummary: 'Keep the payload as the route source of truth while allowing operations to remap the public URL through .env.',
            fallbackHighlights: ['env::VAR::/fallback', 'path override', '.env-driven routing', 'same payload boundary'],
            explanation: $this->explanationProvider->getExplanation('routing', 'env-route-override') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $resolvedPath = trim((string) (Environment::getEnvValue(self::ENV_KEY) ?? ''));
        if ($resolvedPath === '') {
            $resolvedPath = self::FALLBACK_PATH;
        }

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Payload Example' => $this->sourceCodeReader->readProjectRelativeSource(
                    'resources/examples/Routing/EnvRouteOverride/EnvRouteOverridePayload.example.php',
                ),
                'Handler Example' => $this->sourceCodeReader->readProjectRelativeSource(
                    'resources/examples/Routing/EnvRouteOverride/EnvRouteOverrideHandler.example.php',
                ),
                'Docs' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-core/docs/PAYLOAD_ENV_ROUTE_OVERRIDES.md'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Operational Flexibility',
                'title' => 'One payload, environment-specific URL',
                'summary' => 'The payload class remains the canonical route owner, while ops can remap the public path with one env value and still keep the same handler, resource, alternates, and docs surface.',
                'paragraphs' => [
                    'The attribute keeps a safe fallback path directly in PHP, so the route is still readable in code review.',
                    'If the env key is present, Semitexa resolves that value during route discovery and the live route moves without editing the payload source.',
                    'Because the payload still owns the route contract, the sidebar links, alternate JSON representation, and crawler inventory stay aligned with the resolved path.',
                ],
                'columns' => ['Input', 'Value', 'Effect'],
                'rows' => [
                    [
                        ['text' => 'Env key'],
                        ['text' => self::ENV_KEY, 'code' => true],
                        ['text' => 'Controls the externally visible route path'],
                    ],
                    [
                        ['text' => 'Fallback path'],
                        ['text' => self::FALLBACK_PATH, 'code' => true],
                        ['text' => 'Used when the env key is absent', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Resolved path now'],
                        ['text' => $resolvedPath, 'code' => true],
                        ['text' => $resolvedPath === self::FALLBACK_PATH ? 'Running on fallback route' : 'Running on env override route', 'variant' => 'success'],
                    ],
                ],
                'codeSnippet' => "DEMO_ENV_ROUTE_OVERRIDE_PATH=/demo/http/env-override\n\n#[AsPayload(\n    path: 'env::DEMO_ENV_ROUTE_OVERRIDE_PATH::/demo/routing/env-route-override',\n    methods: ['GET'],\n    responseWith: DemoFeatureResource::class,\n)]",
                'note' => 'This is intentionally boring in the best way: the handler does not change, the resource does not change, and the payload still stays the single place where the route contract is declared.',
            ]);
    }
}
