<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\ReadonlyInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureRegistry;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReadonlyInjectionPayload::class, resource: DemoFeatureResource::class)]
final class ReadonlyInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeatureRegistry $featureRegistry;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(ReadonlyInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $registryId = spl_object_id($this->featureRegistry);

        $resultPreview = '<div class="result-preview">'
            . '<p>This handler has three <code>#[InjectAsReadonly]</code> services injected:</p>'
            . '<table class="data-table">'
            . '<thead><tr><th>Service</th><th>Scope</th><th>Object ID</th></tr></thead>'
            . '<tbody>'
            . sprintf('<tr><td>DemoFeatureRegistry</td><td>worker</td><td>#%d</td></tr>', $registryId)
            . sprintf('<tr><td>DemoSourceCodeReader</td><td>worker</td><td>#%d</td></tr>', spl_object_id($this->sourceCodeReader))
            . sprintf('<tr><td>DemoExplanationProvider</td><td>worker</td><td>#%d</td></tr>', spl_object_id($this->explanationProvider))
            . '</tbody></table>'
            . '<p class="note">Object IDs are stable across requests — the same instances are reused for the life of the worker.</p>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('di', 'readonly') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Readonly Injection — Semitexa Demo')
            ->withSection('di')
            ->withSlug('readonly')
            ->withTitle('Readonly Injection')
            ->withSummary('Stateless services share one instance per worker — zero-cost injection after boot.')
            ->withEntryLine('Stateless services share one instance per worker — zero-cost injection after boot.')
            ->withHighlights(['#[InjectAsReadonly]', 'worker-scoped', 'shared instance', 'zero allocation'])
            ->withLearnMoreLabel('See the injection attribute →')
            ->withDeepDiveLabel('Container tiers explained →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
