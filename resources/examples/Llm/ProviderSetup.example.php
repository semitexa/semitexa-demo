<?php

declare(strict_types=1);

namespace App\Infrastructure\Llm;

use Semitexa\Llm\Data\LlmBackend;

// Local Ollama
// LLM_BASE_URL=http://127.0.0.1:11434
// LLM_MODEL=gemma3:4b

// Remote Ollama
// LLM_REMOTE_OLLAMA_URL=http://10.0.0.25:11434
// LLM_REMOTE_OLLAMA_MODEL=gemma4:e2b

$provider = $factory->get(LlmBackend::Local);

if ($provider->healthCheck()) {
    $response = $provider->complete($request);
}
