<?php

declare(strict_types=1);

namespace App\Console;

use Semitexa\Llm\Data\LlmRequest;

$manifest = $registry->buildManifest();
$planner = new Planner();
$session = new ConversationSession();

$request = new LlmRequest(
    systemPrompt: $planner->buildSystemPrompt($manifest),
    userMessage: 'Flush all cache please.',
    history: $session->getHistory(),
);

$llmResponse = $provider->complete($request);
$decision = $planner->parseResponse($llmResponse);

if ($decision->skill !== null) {
    $executor->execute($decision->skill, $decision->arguments, $manifest);
}
