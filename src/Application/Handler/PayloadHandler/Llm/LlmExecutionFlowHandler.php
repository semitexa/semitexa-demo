<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Llm;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Llm\LlmExecutionFlowPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: LlmExecutionFlowPayload::class, resource: DemoFeatureResource::class)]
final class LlmExecutionFlowHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(LlmExecutionFlowPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'The LLM execution path is intentionally staged: manifest, planner prompt, provider response, parsed decision, human confirmation when needed, and only then command execution.',
            'how' => 'The assistant command builds a constrained system prompt from `SkillManifest`, sends the user request plus recent conversation history to the provider, parses the JSON decision into `PlannerResponse`, and passes approved proposals into `SkillExecutor`.',
            'why' => 'This makes the path inspectable. When something goes wrong, you can tell whether the failure came from the provider, the planner decision, policy validation, or the underlying command itself.',
            'keywords' => [
                ['term' => 'Planner', 'definition' => 'Builds the constrained system prompt and parses the provider response back into a typed planner result.'],
                ['term' => 'PlannerResponse', 'definition' => 'Typed decision object: answer, ask, propose_skill, or refuse.'],
                ['term' => 'SkillExecutor', 'definition' => 'Validates manifest membership and arguments, then runs the underlying Symfony command.'],
                ['term' => 'ConversationSession', 'definition' => 'Stores recent user and assistant messages for context and supports clearing the session.'],
            ],
        ];

        return $resource
            ->pageTitle('LLM Execution Flow — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'llm',
                'currentSlug' => 'execution-flow',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('llm')
            ->withSectionLabel('LLM Module')
            ->withSlug('execution-flow')
            ->withTitle('Execution Flow')
            ->withSummary('How a user request becomes a planner decision, a reviewed skill proposal, and finally a real console execution.')
            ->withEntryLine('The important part is not that the model can talk. The important part is that the path from intent to execution is structured, inspectable, and policy-gated.')
            ->withHighlights(['Planner', 'PlannerResponse', 'SkillExecutor', 'ConversationSession'])
            ->withLearnMoreLabel('See the planning flow →')
            ->withDeepDiveLabel('Where the safety boundaries sit →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Execution Stages',
                'title' => 'From prompt to command run',
                'summary' => 'The model does not get arbitrary shell access. It receives a bounded skill catalog and must answer in one of a few typed planner formats.',
                'codeSnippet' => <<<'PHP'
$manifest = $registry->buildManifest();
$systemPrompt = $planner->buildSystemPrompt($manifest);
$request = new LlmRequest(
    systemPrompt: $systemPrompt,
    userMessage: $input,
    history: $session->getHistory(),
);
$llmResponse = $provider->complete($request);
$decision = $planner->parseResponse($llmResponse);
if ($decision->type->value === 'propose_skill' && $decision->skill !== null) {
    $result = $executor->execute($decision->skill, $decision->arguments, $manifest);
}
PHP,
                'columns' => ['Stage', 'Primary class', 'What happens'],
                'rows' => [
                    [
                        ['text' => 'Build skill surface'],
                        ['text' => 'SkillRegistry + SkillManifest', 'code' => true],
                        ['text' => 'Collect allowed skills and compress them into the planner prompt.'],
                    ],
                    [
                        ['text' => 'Ask the provider'],
                        ['text' => 'LlmProviderInterface + LlmRequest', 'code' => true],
                        ['text' => 'Send system prompt, user message, and recent conversation history to the backend.'],
                    ],
                    [
                        ['text' => 'Parse the decision'],
                        ['text' => 'Planner + PlannerResponse', 'code' => true],
                        ['text' => 'Accept only answer, ask, propose_skill, or refuse in structured JSON form.'],
                    ],
                    [
                        ['text' => 'Gate execution'],
                        ['text' => 'AiConfirmationMode + SkillExecutor', 'code' => true],
                        ['text' => 'Check confirmation needs, validate arguments, and reject anything outside the manifest.'],
                    ],
                    [
                        ['text' => 'Maintain context'],
                        ['text' => 'ConversationSession', 'code' => true],
                        ['text' => 'Keep a trimmed in-memory history so the assistant can continue the same operator conversation.'],
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Safety Edges',
                'title' => 'Where the module deliberately says no',
                'summary' => 'The package has several places where it rejects or degrades behavior instead of pretending everything is fine.',
                'rules' => [
                    'If the provider is unhealthy, `ai` stops before opening a misleading assistant loop.',
                    'If the model returns invalid JSON, the planner falls back to a safe typed response instead of blindly executing text.',
                    'If a proposed skill is absent from the manifest or uses disallowed arguments, `SkillExecutor` throws a policy violation.',
                    'If confirmation is required, the operator still gets the final say unless `--yes` was explicitly chosen.',
                ],
            ])
            ->withSourceCode([
                'Assistant Loop Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Llm/AssistantLoop.example.php'),
                'Planner' => $this->sourceCodeReader->readClassSource(\Semitexa\Llm\Planner\Planner::class),
                'Skill Executor' => $this->sourceCodeReader->readClassSource(\Semitexa\Llm\Executor\SkillExecutor::class),
                'Conversation Session' => $this->sourceCodeReader->readClassSource(\Semitexa\Llm\Session\ConversationSession::class),
            ])
            ->withExplanation($explanation);
    }
}
