<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\BasicRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: BasicRoutePayload::class, resource: DemoFeatureResource::class)]
final class BasicRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(BasicRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('routing', 'basic') ?? [];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(BasicRoutePayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        $resultPreview = '<div class="result-preview">'
            . '<p><strong>GET /demo/routing/basic</strong></p>'
            . '<p>Status: <code>200 OK</code></p>'
            . '<p>This page is served by a single <code>#[AsPayload]</code> attribute and a typed handler.</p>'
            . '</div>';

        return $resource
            ->pageTitle('Basic Route — Semitexa Demo')
            ->withSection('routing')
            ->withSlug('basic')
            ->withTitle('Basic Route')
            ->withSummary('Define a route with one attribute — no XML, no YAML, no config files.')
            ->withEntryLine('Define a route with one attribute — no XML, no YAML, no config files.')
            ->withHighlights(['#[AsPayload]', 'responseWith', 'TypedHandlerInterface'])
            ->withLearnMoreLabel('See the code →')
            ->withDeepDiveLabel('How route compilation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreview($resultPreview);
    }
}
