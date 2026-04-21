<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Llm;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Llm\LlmProvidersPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: LlmProvidersPayload::class, resource: DemoFeatureResource::class)]
final class LlmProvidersHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(LlmProvidersPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'llm',
            slug: 'providers',
            entryLine: 'The assistant is only as reliable as the provider boundary under it. `semitexa/llm` keeps that boundary explicit and swappable.',
            learnMoreLabel: 'See the provider layer →',
            deepDiveLabel: 'How backend resolution works →',
            relatedSlugs: [],
            fallbackTitle: 'Providers & Backends',
            fallbackSummary: 'Provider contracts, backend resolution, local vs remote Ollama, and the environment knobs that shape LLM runtime behavior.',
            fallbackHighlights: ['LlmProviderInterface', 'LlmProviderResolver', 'local Ollama', 'remote Ollama'],
            pageTitleSuffix: ' — Semitexa Demo',
            sectionLabel: 'LLM Module',
        );

        // v1 page title is a hand-authored short form ("LLM Providers"), not the full document
        // title ("Providers & Backends"). Override the projector-computed title to preserve parity.
        return $this->projector->project($resource, $spec)
            ->pageTitle('LLM Providers — Semitexa Demo')
            ->withSourceCode([
                'Provider Setup Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Llm/ProviderSetup.example.php'),
                'Provider Contract' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-llm/src/Contract/LlmProviderInterface.php'),
                'Provider Resolver' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-llm/src/Provider/LlmProviderResolver.php'),
                'Local Ollama Provider' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-llm/src/Provider/LocalOllamaProvider.php'),
                'Remote Ollama Provider' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-llm/src/Provider/RemoteOllamaProvider.php'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Runtime Backends',
                'title' => 'How the module reaches an LLM',
                'summary' => 'The built-in implementation focuses on Ollama, but the rest of the module talks to provider contracts, not to hard-coded curl calls scattered through commands.',
                'columns' => ['Backend', 'Resolver path', 'Key env knobs', 'Operational note'],
                'rows' => [
                    [
                        ['text' => 'Local Ollama'],
                        ['text' => 'LlmBackend::Local -> LocalOllamaProvider', 'code' => true],
                        ['text' => 'LLM_BASE_URL, LLM_MODEL, LLM_TIMEOUT, LLM_RETRIES', 'code' => true],
                        ['text' => 'Default self-hosted path for local inference and fast development loops.'],
                    ],
                    [
                        ['text' => 'Remote Ollama'],
                        ['text' => 'LlmBackend::RemoteOllama -> RemoteOllamaProvider', 'code' => true],
                        ['text' => 'LLM_REMOTE_OLLAMA_URL, LLM_REMOTE_OLLAMA_MODEL, timeouts, retries', 'code' => true],
                        ['text' => 'Useful when the model runtime lives on another host or heavier node.'],
                    ],
                    [
                        ['text' => 'Backend-agnostic commands'],
                        ['text' => 'LlmProviderInterface + LlmProviderResolver', 'code' => true],
                        ['text' => 'backend key + provider factory', 'code' => true],
                        ['text' => 'The assistant loop only needs `healthCheck()` and `complete()` from the active provider.'],
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Provider Rules',
                'title' => 'What the built-in providers already handle',
                'summary' => 'The provider classes are thin, but they own several practical concerns that should not leak into planner or command code.',
                'rules' => [
                    'They build the provider-specific HTTP payload from `LlmRequest` and normalize the response into `LlmResponse`.',
                    'They run a health check before interactive use so the operator gets a clean failure early.',
                    'They expose retries, timeouts, base URL, and model selection through environment-backed config.',
                    'They report latency and token usage when the backend returns that metadata.',
                ],
            ]);
    }
}
