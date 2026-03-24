<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\TypedHandlerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: TypedHandlerPayload::class, resource: DemoFeatureResource::class)]
final class TypedHandlerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(TypedHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('routing', 'typed-handler') ?? [];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(TypedHandlerPayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        $typedExample = 'public function handle(TypedHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource';
        $untypedExample = 'public function handle(object $payload, ResourceInterface $resource): ResourceInterface';

        $resultPreview = '<div class="result-preview">'
            . '<p><strong>Typed vs Untyped Handler Signature</strong></p>'
            . '<div class="result-preview__comparison">'
            . '<div class="result-preview__good">'
            . '<p class="result-preview__label">✓ TypedHandlerInterface</p>'
            . '<code>' . htmlspecialchars($typedExample, ENT_QUOTES) . '</code>'
            . '</div>'
            . '<div class="result-preview__bad">'
            . '<p class="result-preview__label">✗ Legacy HandlerInterface</p>'
            . '<code>' . htmlspecialchars($untypedExample, ENT_QUOTES) . '</code>'
            . '</div>'
            . '</div>'
            . '<p>Concrete types = full IDE support, static analysis, boot-time validation.</p>'
            . '</div>';

        return $resource
            ->pageTitle('Typed Handler — Semitexa Demo')
            ->withSection('routing')
            ->withSlug('typed-handler')
            ->withTitle('Typed Handler')
            ->withSummary('Concrete types in handle() — no instanceof, no casting, no guessing.')
            ->withEntryLine('Handlers declare concrete Payload and Resource types — the framework validates signatures at boot.')
            ->withHighlights(['TypedHandlerInterface', '#[AsPayloadHandler]', 'HandlerReflectionCache', 'concrete types'])
            ->withLearnMoreLabel('See the typed signature →')
            ->withDeepDiveLabel('How reflection validation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreview($resultPreview);
    }
}
