<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\AssetPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: AssetPayload::class, resource: DemoFeatureResource::class)]
final class AssetHandler implements TypedHandlerInterface
{
    private const DEMO_ASSETS = [
        ['file' => 'css/demo.css',              'inject' => 'head',  'purpose' => 'Core layout, typography, cards'],
        ['file' => 'css/disclosure.css',        'inject' => 'head',  'purpose' => 'Expandable sections, drawers, tooltips'],
        ['file' => 'css/feature-explorer.css',  'inject' => 'head',  'purpose' => 'Three-panel grid layout'],
        ['file' => 'css/code-highlight.css',    'inject' => 'head',  'purpose' => 'Code block tabs and syntax'],
        ['file' => 'js/demo-app.js',            'inject' => 'body',  'purpose' => 'Visited tracking, feature tree'],
        ['file' => 'js/disclosure.js',          'inject' => 'body',  'purpose' => 'Expand/collapse, drawer, tooltip'],
        ['file' => 'js/code-tabs.js',           'inject' => 'body',  'purpose' => 'Tab switching, clipboard copy'],
        ['file' => 'js/live-result.js',         'inject' => 'body',  'purpose' => 'Interactive HTTP client panel'],
        ['file' => 'js/sse-demo.js',            'inject' => 'body',  'purpose' => 'SSE connect/disconnect UI'],
    ];

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(AssetPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $manifestSnippet = <<<'JSON'
{
  "$schema": "semitexa://asset-manifest/v2",
  "include": [
    { "glob": "css/**/*.css", "inject": "head" },
    { "glob": "js/**/*.js",   "inject": "body" }
  ]
}
JSON;

        $explanation = $this->explanationProvider->getExplanation('rendering', 'assets') ?? [];

        $assetsPath = dirname(__DIR__, 5) . '/Static/assets.json';
        $assetsContent = is_readable($assetsPath) ? file_get_contents($assetsPath) : false;

        $sourceCode = [
            'assets.json' => $assetsContent !== false ? $assetsContent : '// assets.json not found',
        ];

        return $resource
            ->pageTitle('Asset Pipeline — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'assets',
                'infoWhat' => $explanation['what'] ?? 'The asset pipeline expands manifest globs, versions files, and injects them into head or body automatically.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('assets')
            ->withTitle('Asset Pipeline')
            ->withSummary('Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.')
            ->withEntryLine('Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.')
            ->withHighlights(['assets.json', 'asset_head()', 'asset_body()', 'glob patterns', 'versioning'])
            ->withLearnMoreLabel('See the asset manifest →')
            ->withDeepDiveLabel('Asset pipeline internals →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Asset Manifest',
                'title' => sprintf('%d assets wired automatically', count(self::DEMO_ASSETS)),
                'summary' => 'The framework resolves manifest globs, versions files, and injects them into the correct head or body slot.',
                'codeSnippet' => $manifestSnippet,
                'columns' => ['File', 'Inject point', 'Purpose'],
                'rows' => array_map(
                    static fn (array $asset): array => [
                        ['text' => $asset['file'], 'code' => true],
                        ['text' => $asset['inject'], 'code' => true],
                        ['text' => $asset['purpose']],
                    ],
                    self::DEMO_ASSETS,
                ),
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
