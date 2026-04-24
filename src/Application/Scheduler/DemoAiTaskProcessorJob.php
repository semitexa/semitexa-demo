<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Scheduler;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Domain\Repository\DemoAiTaskRepositoryInterface;
use Semitexa\Demo\Application\Service\DemoAiTextProcessor;
use Semitexa\Scheduler\Attribute\AsScheduledJob;
use Semitexa\Scheduler\Contract\ScheduledJobInterface;
use Semitexa\Scheduler\Domain\Value\ScheduledJobContext;

#[AsScheduledJob(
    key: 'demo.ai_task_processor',
    cronExpression: '*/10 * * * *',
    overlapPolicy: 'skip',
)]
final class DemoAiTaskProcessorJob implements ScheduledJobInterface
{
    #[InjectAsReadonly]
    protected DemoAiTaskRepositoryInterface $aiTaskRepository;

    #[InjectAsReadonly]
    protected DemoAiTextProcessor $processor;

    public function handle(ScheduledJobContext $context): void
    {
        if (!isset($this->aiTaskRepository) || !isset($this->processor)) {
            return;
        }

        $pending = $this->aiTaskRepository->findPending(1);
        if ($pending === []) {
            $running = $this->aiTaskRepository->findByStatus('running');
            $pending = array_slice($running, 0, 1);
        }

        foreach ($pending as $task) {
            $this->processor->processNextStage($task->getId());
        }
    }
}
