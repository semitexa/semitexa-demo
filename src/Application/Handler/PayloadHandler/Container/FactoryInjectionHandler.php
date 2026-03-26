<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Container;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Container\FactoryInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: FactoryInjectionPayload::class, resource: DemoFeatureResource::class)]
final class FactoryInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(FactoryInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>Factory injection gives you a <strong>callable</strong> that produces a fresh instance on every call. '
            . 'The factory itself is worker-scoped; only the produced objects are new.</p>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "#[InjectAsFactory]\n"
                . "protected \Closure \$mailerFactory;\n\n"
                . "public function handle(...): ... {\n"
                . "    \$mailer = (\$this->mailerFactory)();\n"
                . "    // \$mailer is a brand-new instance\n"
                . "}"
            )
            . '</pre>'
            . '<p class="note">Use factories when a service is expensive to keep alive but cheap to construct on demand, '
            . 'or when each call needs isolated configuration.</p>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('di', 'factory') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Factory Injection — Semitexa Demo')
            ->withSection('di')
            ->withSlug('factory')
            ->withTitle('Factory Injection')
            ->withSummary('On-demand service creation — the factory is shared, but each call produces a new instance.')
            ->withEntryLine('On-demand service creation — the factory is shared, but each call produces a new instance.')
            ->withHighlights(['#[InjectAsFactory]', 'factory callable', 'on-demand', 'lazy instantiation'])
            ->withLearnMoreLabel('See factory injection →')
            ->withDeepDiveLabel('Lazy instantiation patterns →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
