<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\BlogAiNativeResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;

#[AsPublicPayload(responseWith: BlogAiNativeResource::class, produces: ['text/html'], path: DemoBlogCatalog::AI_NATIVE_PATH, methods: ['GET'])]
final class BlogAiNativePayload
{
    protected string $scenario = 'boundary';
    protected string $rule = 'buggy';

    public function getScenario(): string { return $this->scenario; }
    public function getRule(): string { return $this->rule; }

    public function setScenario(string $scenario): void
    {
        $this->scenario = in_array($scenario, ['below', 'boundary', 'above'], true) ? $scenario : 'boundary';
    }

    public function setRule(string $rule): void
    {
        $this->rule = $rule === 'fixed' ? 'fixed' : 'buggy';
    }
}
