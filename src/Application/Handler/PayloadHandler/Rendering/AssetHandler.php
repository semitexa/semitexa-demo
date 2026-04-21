<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Rendering\AssetPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;

#[AsPayloadHandler(payload: AssetPayload::class, resource: DemoFeatureResource::class)]
final class AssetHandler implements TypedHandlerInterface
{
    private const MANIFEST_SNIPPET = <<<'JSON'
    {
      "$schema": "semitexa://asset-manifest/v2",
      "include": [
        { "glob": "css/**/*.css", "inject": "head" },
        { "glob": "js/**/*.js",   "inject": "body" }
      ]
    }
    JSON;

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
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(AssetPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'assets',
            entryLine: 'Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.',
            learnMoreLabel: 'See the asset manifest →',
            deepDiveLabel: 'Asset pipeline internals →',
            relatedSlugs: [],
            fallbackTitle: 'Asset Pipeline',
            fallbackSummary: 'Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.',
            fallbackHighlights: ['assets.json', 'asset_head()', 'asset_body()', 'glob patterns', 'versioning'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'assets') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $assetsPath = dirname(__DIR__, 5) . '/Static/assets.json';
        $assetsContent = is_readable($assetsPath) ? file_get_contents($assetsPath) : false;

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'assets.json' => $assetsContent !== false ? $assetsContent : '// assets.json not found',
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Asset Manifest',
                'title' => sprintf('%d assets wired automatically', count(self::DEMO_ASSETS)),
                'summary' => 'The framework resolves manifest globs, versions files, and injects them into the correct head or body slot.',
                'codeSnippet' => self::MANIFEST_SNIPPET,
                'columns' => ['File', 'Inject point', 'Purpose'],
                'rows' => array_map(
                    static fn (array $asset): array => [
                        ['text' => $asset['file'], 'code' => true],
                        ['text' => $asset['inject'], 'code' => true],
                        ['text' => $asset['purpose']],
                    ],
                    self::DEMO_ASSETS,
                ),
            ]);
    }
}
