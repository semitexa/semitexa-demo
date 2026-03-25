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

        if ($submitted && $taskId !== null) {
            $resultPreview = '<div class="result-preview result-preview--success">'
                . '<p><strong>Task submitted!</strong> The cron job picks it up every 10 seconds.</p>'
                . '<p>Task ID: <code>' . htmlspecialchars(substr($taskId, 0, 16)) . '…</code></p>'
                . '<p><a href="/demo/rendering/reactive-ai" class="btn btn--secondary">Watch pipeline →</a></p>'
                . '</div>';
        } elseif ($errorMessage !== null) {
            $resultPreview = '<div class="result-preview result-preview--error">'
                . '<p class="error-message">' . htmlspecialchars($errorMessage) . '</p>'
                . $this->buildForm($inputText)
                . '</div>';
        } else {
            $resultPreview = '<div class="result-preview">'
                . '<p>Enter text below and submit. The AI pipeline will process it stage by stage.</p>'
                . $this->buildForm('')
                . '</div>';
        }

        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-ai') ?? [];

        $sourceCode = [
            'AiTaskSubmitPayload' => $this->sourceCodeReader->readClassSource(AiTaskSubmitPayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Submit AI Task — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('reactive-ai')
            ->withTitle('Submit AI Task')
            ->withSummary('Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.')
            ->withEntryLine('Enter text to process through the 4-stage AI pipeline.')
            ->withHighlights(['DemoAiTask', 'pending → cron pickup', 'stage_results JSON', 'refreshInterval: 2'])
            ->withLearnMoreLabel('Watch the pipeline →')
            ->withDeepDiveLabel('Processor architecture →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }

    private function buildForm(string $defaultText): string
    {
        return '<form method="POST" action="/demo/rendering/reactive-ai/submit" style="margin-top:1rem">'
            . '<div class="form-group">'
            . '<label for="inputText" class="form-label">Text to process</label>'
            . '<textarea id="inputText" name="inputText" class="form-control" rows="4" maxlength="2000" placeholder="Enter any text to run through the AI pipeline…">'
            . htmlspecialchars($defaultText)
            . '</textarea>'
            . '</div>'
            . '<button type="submit" class="btn btn--primary" style="margin-top:0.75rem">Submit task →</button>'
            . '</form>';
    }
}
