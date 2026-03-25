<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\SeoPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SeoPayload::class, resource: DemoFeatureResource::class)]
final class SeoHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(SeoPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>This page\'s own <code>&lt;title&gt;</code> and meta tags are set by its handler. '
            . 'No Twig blocks to override, no base template tricks.</p>'
            . '<table class="data-table">'
            . '<thead><tr><th>Tag</th><th>Source</th><th>Value on this page</th></tr></thead>'
            . '<tbody>'
            . '<tr><td><code>&lt;title&gt;</code></td><td><code>pageTitle()</code></td><td>SEO — Semitexa Demo</td></tr>'
            . '<tr><td><code>og:title</code></td><td><code>withMeta()</code></td><td>SEO — Semitexa Demo</td></tr>'
            . '<tr><td><code>og:description</code></td><td><code>withMeta()</code></td><td>Set title, description…</td></tr>'
            . '<tr><td><code>og:type</code></td><td>framework default</td><td>website</td></tr>'
            . '<tr><td><code>canonical</code></td><td>framework default</td><td>/demo/rendering/seo</td></tr>'
            . '</tbody></table>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "return \$resource\n"
                . "    ->pageTitle('SEO — Semitexa Demo')\n"
                . "    ->withMeta('description', 'Set title, description, and Open Graph tags…')\n"
                . "    ->withMeta('og:image', '/static/og-card.png');"
            )
            . '</pre>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'seo') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('SEO — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('seo')
            ->withTitle('SEO')
            ->withSummary('Set title, description, and Open Graph tags from your handler — no template hacks needed.')
            ->withEntryLine('Set title, description, and Open Graph tags from your handler — no template hacks needed.')
            ->withHighlights(['pageTitle()', 'withMeta()', 'Open Graph', 'canonical URL', 'structured data'])
            ->withLearnMoreLabel('See SEO methods →')
            ->withDeepDiveLabel('SEO pipeline internals →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
