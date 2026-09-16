<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Reactive;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlSlotResponse;

/**
 * NOT RENDERED BY ANY PAGE, and that is the point worth writing down.
 *
 * `lint:deferred-slots` reports this slot: it declares `deferred: true` and no
 * template calls `layout_slot_deferred('reactive_ai_task')`, so the flag — and the
 * refreshInterval and clientModules beside it — describe a page that does not
 * exist. What the demo page at this feature's route actually renders is a
 * preview component plus this class's SOURCE, as teaching material.
 *
 * So read it as a sample, not as live wiring. Making it live means a slot
 * handler to supply its context and a template call to place it, which is a
 * demo feature rather than a framework fix — filed as tk-slots-nobody-renders.
 */
#[AsSlotResource(
    handle: 'demo_reactive_ai',
    slot: 'reactive_ai_task',
    template: '@project-layouts-semitexa-demo/reactive/ai-task.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/reactive/ai-task.skeleton.html.twig',
    refreshInterval: 2,
    clientModules: ['@project-static-semitexa-demo/reactive/ai-task-pipeline.js'],
)]
final class ReactiveAiTaskSlot extends HtmlSlotResponse
{
    public function withStatus(string $status): static { return $this->with('status', $status); }
    public function withStages(array $stages): static { return $this->with('stages', $stages); }
    public function withStageResults(array $results): static { return $this->with('stageResults', $results); }
    public function withInputText(string $text): static { return $this->with('inputText', $text); }
}
