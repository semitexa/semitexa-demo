<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use JsonException;
use Random\RandomException;
use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Domain\Repository\DemoAiTaskRepositoryInterface;

#[AsService]
final class DemoAiTextProcessor
{
    private const array STAGES = ['parse', 'analyze', 'generate', 'format'];

    #[InjectAsReadonly]
    protected ?DemoAiTaskRepositoryInterface $aiTaskRepository = null;

    /**
     * Advance a pending AI task by one processing stage.
     * @throws RandomException|JsonException
     */
    public function processNextStage(string $taskId): bool
    {
        $task = $this->aiTaskRepository?->findById($taskId);
        if ($task === null) {
            return false;
        }

        $stageResults = [];
        if (!empty($task->getStageResults())) {
            try {
                $stageResults = json_decode($task->getStageResults(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $stageResults = [];
            }
        }

        $completedCount = count(array_intersect(self::STAGES, array_keys($stageResults)));
        if ($completedCount >= count(self::STAGES)) {
            return $this->aiTaskRepository?->updateStatus($taskId, 'completed') === true;
        }

        $nextStage = null;
        foreach (self::STAGES as $stageName) {
            if (!array_key_exists($stageName, $stageResults)) {
                $nextStage = $stageName;
                break;
            }
        }
        if ($nextStage === null) {
            return $this->aiTaskRepository?->updateStatus($taskId, 'completed') === true;
        }

        $stageResults[$nextStage] = [
            'status' => 'done',
            'tokens' => random_int(20, 200),
            'ms'     => random_int(80, 400),
        ];

        $saved = $this->aiTaskRepository?->updateStageResults($taskId, json_encode($stageResults, JSON_THROW_ON_ERROR));
        if ($saved !== true) {
            return false;
        }

        $newStatus = count(array_intersect(self::STAGES, array_keys($stageResults))) >= count(self::STAGES)
            ? 'completed'
            : 'running';

        return $this->aiTaskRepository?->updateStatus($taskId, $newStatus) === true;
    }

    public function getStages(): array
    {
        return self::STAGES;
    }
}
