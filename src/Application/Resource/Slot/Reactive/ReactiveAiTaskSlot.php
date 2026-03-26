<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Reactive;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_reactive_ai',
    slot: 'reactive_ai_task',
    template: '@project-layouts-semitexa-demo/reactive/ai-task.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/reactive/ai-task.skeleton.html.twig',
    refreshInterval: 2,
    clientModules: ['reactive/ai-task-pipeline.js'],
)]
final class ReactiveAiTaskSlot extends HtmlSlotResponse
{
    public function withStatus(string $status): static { return $this->with('status', $status); }
    public function withStages(array $stages): static { return $this->with('stages', $stages); }
    public function withStageResults(array $results): static { return $this->with('stageResults', $results); }
    public function withInputText(string $text): static { return $this->with('inputText', $text); }
}
