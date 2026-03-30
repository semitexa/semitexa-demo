<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAiTaskResource;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoAiTaskRepository;
use Semitexa\Demo\Application\Payload\Request\Rendering\AiTaskSubmitPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoAiTextProcessor;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: AiTaskSubmitPayload::class, resource: DemoFeatureResource::class)]
final class AiTaskSubmitHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoAiTaskRepository $aiTaskRepository;

    #[InjectAsReadonly]
    protected DemoAiTextProcessor $aiTextProcessor;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(AiTaskSubmitPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $inputText = trim($payload->getInputText() ?? '');
        $submitted = false;
        $taskId = null;
        $errorMessage = null;

        if ($inputText !== '') {
            if (strlen($inputText) > 2000) {
                $errorMessage = 'Input text must not exceed 2000 characters.';
            } else {
                $task = new DemoAiTaskResource();
                $task->tenant_id = 'demo';
                $task->input_text = $inputText;
                $task->status = 'pending';
                $task->stages = json_encode($this->aiTextProcessor->getStages(), JSON_THROW_ON_ERROR);

                $this->aiTaskRepository->save($task);
                $taskId = $task->id ?? null;
                $submitted = true;
            }
        }

        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-ai') ?? [];

        $sourceCode = [
            'AiTaskSubmitPayload' => $this->sourceCodeReader->readClassSource(AiTaskSubmitPayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Submit AI Task — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'reactive-ai',
                'infoWhat' => $explanation['what'] ?? 'The submit screen uses the same demo shell as the live pipeline page, so navigation and contextual framing stay intact before and after task creation.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('reactive-ai')
            ->withTitle('Submit AI Task')
            ->withSummary('Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.')
            ->withEntryLine('Enter text to process through the 4-stage AI pipeline.')
            ->withHighlights(['DemoAiTask', 'pending → cron pickup', 'stage_results JSON', 'refreshInterval: 2'])
            ->withLearnMoreLabel('Watch the pipeline →')
            ->withDeepDiveLabel('Processor architecture →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/ai-task-submit.html.twig', [
                'state' => $submitted && $taskId !== null ? 'success' : ($errorMessage !== null ? 'error' : 'idle'),
                'message' => $submitted && $taskId !== null
                    ? 'The cron worker will pick this task up and process it stage by stage.'
                    : ($errorMessage ?? 'Enter text below and submit it into the four-stage AI pipeline.'),
                'taskId' => $taskId !== null ? substr($taskId, 0, 16) . '…' : null,
                'inputText' => $submitted ? '' : $inputText,
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
