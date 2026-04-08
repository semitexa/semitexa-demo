<?php

declare(strict_types=1);

namespace App\Console;

use Semitexa\Llm\Data\LlmRequest;
use Semitexa\Llm\Data\PlannerResponseType;
use Semitexa\Llm\Planner\Planner;
use Semitexa\Llm\Session\ConversationSession;

// Resolve these collaborators from your container/bootstrap before running the example.
$registry = $registry ?? throw new \LogicException('Resolve $registry from your application container.');
$provider = $provider ?? throw new \LogicException('Resolve $provider from your LLM provider factory.');
$executor = $executor ?? throw new \LogicException('Resolve $executor from your application container.');

$userMessage = 'Flush all cache please.';
$manifest = $registry->buildManifest();
$planner = new Planner();
$session = new ConversationSession();
$session->addUserMessage($userMessage);

while (true) {
    $request = new LlmRequest(
        systemPrompt: $planner->buildSystemPrompt($manifest),
        userMessage: $userMessage,
        history: $session->getHistory(),
    );

    $llmResponse = $provider->complete($request);
    $decision = $planner->parseResponse($llmResponse, $userMessage);

    if ($decision->type !== PlannerResponseType::ProposeSkill || $decision->skill === null) {
        $session->addAssistantMessage($decision->message ?? $decision->reason);
        break;
    }

    $result = $executor->execute($decision->skill, $decision->arguments, $manifest);
    $session->addAssistantMessage($result->output);
}
