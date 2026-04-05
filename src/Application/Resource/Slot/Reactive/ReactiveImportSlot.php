<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Reactive;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_reactive_import',
    slot: 'reactive_import',
    template: '@project-layouts-semitexa-demo/reactive/import.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/reactive/import.skeleton.html.twig',
    refreshInterval: 2,
    clientModules: ['@project-static-semitexa-demo/reactive/import-counters.js'],
)]
final class ReactiveImportSlot extends HtmlSlotResponse
{
    public function withStatus(string $status): static { return $this->with('status', $status); }
    public function withProgress(int $percent): static { return $this->with('progress', $percent); }
    public function withMessage(string $message): static { return $this->with('message', $message); }
    public function withTotalRows(int $total): static { return $this->with('totalRows', $total); }
}
