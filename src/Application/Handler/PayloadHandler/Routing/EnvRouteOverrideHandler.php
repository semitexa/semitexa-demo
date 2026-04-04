<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Environment;
use Semitexa\Demo\Application\Payload\Request\Routing\EnvRouteOverridePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: EnvRouteOverridePayload::class, resource: DemoFeatureResource::class)]
final class EnvRouteOverrideHandler implements TypedHandlerInterface
{
    private const ENV_KEY = 'DEMO_ENV_ROUTE_OVERRIDE_PATH';
    private const FALLBACK_PATH = '/demo/routing/env-route-override';

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(EnvRouteOverridePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('routing', 'env-route-override') ?? [];
        $resolvedPath = trim((string) (Environment::getEnvValue(self::ENV_KEY) ?? ''));
        if ($resolvedPath === '') {
            $resolvedPath = self::FALLBACK_PATH;
        }

        $sourceCode = [
            'Payload Example' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/Routing/EnvRouteOverride/EnvRouteOverridePayload.example.php',
            ),
            'Handler Example' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/Routing/EnvRouteOverride/EnvRouteOverrideHandler.example.php',
            ),
            'Docs' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-core/docs/PAYLOAD_ENV_ROUTE_OVERRIDES.md'),
        ];

        return $resource
            ->pageTitle('Env Route Override — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'env-route-override',
                'infoWhat' => $explanation['what'] ?? 'The route contract stays on the payload DTO, but the public path can still be changed through .env.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('env-route-override')
            ->withTitle('Env Route Override')
            ->withSummary('Keep the payload as the route source of truth while allowing operations to remap the public URL through .env.')
            ->withEntryLine('The route still lives in PHP, but deployment can move the public URL without reopening the payload class.')
            ->withHighlights(['env::VAR::/fallback', 'path override', '.env-driven routing', 'same payload boundary'])
            ->withLearnMoreLabel('See env override pattern →')
            ->withDeepDiveLabel('How resolved route metadata works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
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
