<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Llm;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Llm\LlmProvidersPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: LlmProvidersPayload::class, resource: DemoFeatureResource::class)]
final class LlmProvidersHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(LlmProvidersPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'llm',
            'providers',
            'Providers & Backends',
            'Provider contracts, backend resolution, local vs remote Ollama, and the environment knobs that shape LLM runtime behavior.',
            ['LlmProviderInterface', 'LlmProviderResolver', 'local Ollama', 'remote Ollama'],
        );

        return $resource
            ->pageTitle('LLM Providers — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'llm',
                'currentSlug' => 'providers',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('llm')
            ->withSectionLabel('LLM Module')
            ->withSlug('providers')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('The assistant is only as reliable as the provider boundary under it. `semitexa/llm` keeps that boundary explicit and swappable.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the provider layer →')
            ->withDeepDiveLabel('How backend resolution works →')
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
            ])
            ->withSourceCode([
                'Provider Setup Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Llm/ProviderSetup.example.php'),
                'Provider Contract' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-llm/src/Contract/LlmProviderInterface.php'),
                'Provider Resolver' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-llm/src/Provider/LlmProviderResolver.php'),
                'Local Ollama Provider' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-llm/src/Provider/LocalOllamaProvider.php'),
                'Remote Ollama Provider' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-llm/src/Provider/RemoteOllamaProvider.php'),
            ]);
    }
}
