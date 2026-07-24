<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Domain\Model\DemoAiTask;
use Semitexa\Demo\Domain\Repository\DemoAiTaskRepositoryInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Rendering\AiTaskSubmitPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoAiTextProcessor;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: AiTaskSubmitPayload::class, resource: DemoFeatureResource::class)]
final class AiTaskSubmitHandler implements TypedHandlerInterface
{
    private const MAX_INPUT_LENGTH = 2000;

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoAiTaskRepositoryInterface $aiTaskRepository;

    #[InjectAsReadonly]
    protected DemoAiTextProcessor $aiTextProcessor;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(AiTaskSubmitPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $submission = $this->processSubmission(trim($payload->getInputText() ?? ''));

        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'reactive-ai',
            entryLine: 'Enter text to process through the 4-stage AI pipeline.',
            learnMoreLabel: 'Watch the pipeline →',
            deepDiveLabel: 'Processor architecture →',
            relatedSlugs: [],
            fallbackTitle: 'Reactive AI Task',
            fallbackSummary: 'Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.',
            fallbackHighlights: ['DemoAiTask', 'stage-by-stage', 'refreshInterval: 2', 'user-triggered → cron pickup'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'reactive-ai') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->pageTitle('Submit AI Task — Semitexa Demo')
            ->withTitle('Submit AI Task')
            ->withSourceCode([
                'AiTaskSubmitPayload' => $this->sourceCodeReader->readClassSource(AiTaskSubmitPayload::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/ai-task-submit.html.twig', $submission);
    }

    /**
     * @return array{state: string, message: string, taskId: ?string, inputText: string}
     */
    private function processSubmission(string $inputText): array
    {
        if ($inputText === '') {
            return [
                'state' => 'idle',
                'message' => 'Enter text below and submit it into the four-stage AI pipeline.',
                'taskId' => null,
                'inputText' => '',
            ];
        }

        if (strlen($inputText) > self::MAX_INPUT_LENGTH) {
            return [
                'state' => 'error',
                'message' => 'Input text must not exceed ' . self::MAX_INPUT_LENGTH . ' characters.',
                'taskId' => null,
                'inputText' => $inputText,
            ];
        }

        $task = $this->persistTask($inputText);
        $taskId = $task->getId() !== '' ? $task->getId() : null;

        return [
            'state' => 'success',
            'message' => 'The cron worker will pick this task up and process it stage by stage.',
            'taskId' => $taskId !== null ? substr($taskId, 0, 16) . '…' : null,
            'inputText' => '',
        ];
    }

    private function persistTask(string $inputText): DemoAiTask
    {
        $task = new DemoAiTask();
        $task->setTenantId('demo');
        $task->setInputText($inputText);
        $task->setStatus('pending');
        $task->setStages(json_encode($this->aiTextProcessor->getStages(), JSON_THROW_ON_ERROR));

        return $this->aiTaskRepository->save($task);
    }
}
