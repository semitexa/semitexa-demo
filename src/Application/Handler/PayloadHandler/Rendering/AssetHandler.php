<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\AssetPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
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

    public function handle(AssetPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $rows = '';
        foreach (self::DEMO_ASSETS as $asset) {
            $rows .= sprintf(
                '<tr><td><code>%s</code></td><td><code>%s</code></td><td>%s</td></tr>',
                htmlspecialchars($asset['file']),
                htmlspecialchars($asset['inject']),
                htmlspecialchars($asset['purpose']),
            );
        }

        $manifestSnippet = <<<'JSON'
{
  "$schema": "semitexa://asset-manifest/v2",
  "include": [
    { "glob": "css/**/*.css", "inject": "head" },
    { "glob": "js/**/*.js",   "inject": "body" }
  ]
}
JSON;

        $resultPreview = '<div class="result-preview">'
            . '<p>This package declares its assets with a two-line <code>assets.json</code> manifest. '
            . 'The framework resolves the globs, versions the files, and injects them into the correct '
            . '<code>&lt;head&gt;</code> or <code>&lt;body&gt;</code> position automatically.</p>'
            . '<pre class="code-inline">' . htmlspecialchars($manifestSnippet) . '</pre>'
            . '<p>' . count(self::DEMO_ASSETS) . ' assets registered in this demo package:</p>'
            . '<table class="data-table">'
            . '<thead><tr><th>File</th><th>Inject point</th><th>Purpose</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'assets') ?? [];

        $assetsPath = dirname(__DIR__, 5) . '/Static/assets.json';
        $assetsContent = is_readable($assetsPath) ? file_get_contents($assetsPath) : false;

        $sourceCode = [
            'assets.json' => $assetsContent !== false ? $assetsContent : '// assets.json not found',
        ];

        return $resource
            ->pageTitle('Asset Pipeline — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('assets')
            ->withTitle('Asset Pipeline')
            ->withSummary('Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.')
            ->withEntryLine('Declare assets with glob patterns in assets.json — served, versioned, and injected automatically.')
            ->withHighlights(['assets.json', 'asset_head()', 'asset_body()', 'glob patterns', 'versioning'])
            ->withLearnMoreLabel('See the asset manifest →')
            ->withDeepDiveLabel('Asset pipeline internals →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
