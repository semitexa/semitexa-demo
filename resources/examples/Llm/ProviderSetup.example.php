<?php

declare(strict_types=1);

namespace App\Infrastructure\Llm;

use Semitexa\Llm\Data\LlmBackend;
use Semitexa\Llm\Data\LlmRequest;

// Local Ollama
// LLM_BASE_URL=http://127.0.0.1:11434
// LLM_MODEL=gemma3:4b

// Remote Ollama
// LLM_REMOTE_OLLAMA_URL=http://10.0.0.25:11434
// LLM_REMOTE_OLLAMA_MODEL=gemma4:e2b

// Resolve the configured provider factory from your container/bootstrap.
$factory = $factory ?? throw new \LogicException('Resolve $factory from your application container.');
$request = new LlmRequest(
    systemPrompt: 'You are a Semitexa framework assistant.',
    userMessage: 'Summarize the current deployment health.',
);

$provider = $factory->get(LlmBackend::Local);

if ($provider->healthCheck()) {
    $response = $provider->complete($request);
}
