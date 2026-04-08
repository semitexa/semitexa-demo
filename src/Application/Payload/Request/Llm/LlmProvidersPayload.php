<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Llm;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/llm/providers',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'llm',
    title: 'Providers & Backends',
    slug: 'providers',
    summary: 'Provider contracts, backend resolution, local vs remote Ollama, and the environment knobs that shape LLM runtime behavior.',
    order: 4,
    highlights: ['LlmProviderInterface', 'LlmProviderResolver', 'local Ollama', 'remote Ollama'],
    entryLine: 'The assistant is only as reliable as the provider boundary under it. `semitexa/llm` keeps that boundary explicit and swappable.',
    learnMoreLabel: 'See the provider layer →',
    deepDiveLabel: 'How backend resolution works →',
)]
final class LlmProvidersPayload
{
}
