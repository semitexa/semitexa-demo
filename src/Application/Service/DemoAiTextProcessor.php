<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use JsonException;
use Random\RandomException;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoAiTaskRepository;

final class DemoAiTextProcessor
{
    private const array STAGES = ['parse', 'analyze', 'generate', 'format'];

    #[InjectAsReadonly]
    protected ?DemoAiTaskRepository $aiTaskRepository = null;

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
        if (!empty($task->stage_results)) {
            try {
                $stageResults = json_decode($task->stage_results, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $stageResults = [];
            }
        }

        $completedCount = count($stageResults);
        if ($completedCount >= count(self::STAGES)) {
            $this->aiTaskRepository?->updateStatus($taskId, 'completed');
            return true;
        }

        $nextStage = self::STAGES[$completedCount];
        $stageResults[$nextStage] = [
            'status' => 'done',
            'tokens' => random_int(20, 200),
            'ms'     => random_int(80, 400),
        ];

        $this->aiTaskRepository?->updateStageResults($taskId, json_encode($stageResults, JSON_THROW_ON_ERROR));

        if (count($stageResults) >= count(self::STAGES)) {
            $this->aiTaskRepository?->updateStatus($taskId, 'completed');
        } else {
            $this->aiTaskRepository?->updateStatus($taskId, 'running');
        }

        return true;
    }

    public function getStages(): array
    {
        return self::STAGES;
    }
}
