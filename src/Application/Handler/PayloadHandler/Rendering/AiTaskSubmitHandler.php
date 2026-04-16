<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Domain\Model\DemoAiTask;
use Semitexa\Demo\Domain\Repository\DemoAiTaskRepositoryInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\AiTaskSubmitPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoAiTextProcessor;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: AiTaskSubmitPayload::class, resource: DemoFeatureResource::class)]
final class AiTaskSubmitHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoAiTaskRepositoryInterface $aiTaskRepository;

    #[InjectAsReadonly]
    protected DemoAiTextProcessor $aiTextProcessor;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

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
                $task = new DemoAiTask();
                $task->setTenantId('demo');
                $task->setInputText($inputText);
                $task->setStatus('pending');
                $task->setStages(json_encode($this->aiTextProcessor->getStages(), JSON_THROW_ON_ERROR));

                $task = $this->aiTaskRepository->save($task);
                $taskId = $task->getId() !== '' ? $task->getId() : null;
                $submitted = true;
            }
        }

        $presentation = $this->documents->resolve(
            'rendering',
            'reactive-ai',
            'Reactive AI Task',
            'Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.',
            ['DemoAiTask', 'stage-by-stage', 'refreshInterval: 2', 'user-triggered → cron pickup'],
        );
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
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('reactive-ai')
            ->withTitle('Submit AI Task')
            ->withSummary($presentation->summary)
            ->withEntryLine('Enter text to process through the 4-stage AI pipeline.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
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
