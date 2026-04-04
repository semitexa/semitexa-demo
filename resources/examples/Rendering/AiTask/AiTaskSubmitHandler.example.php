<?php

declare(strict_types=1);

namespace App\Application\Handler\Rendering;

use App\Application\Payload\Rendering\AiTaskSubmitPayload;
use App\Application\Resource\Page\AiTaskPageResource;
use App\Domain\Ai\AiTaskRunnerInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: AiTaskSubmitPayload::class, resource: AiTaskPageResource::class)]
final class AiTaskSubmitHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected AiTaskRunnerInterface $runner;

    public function handle(AiTaskSubmitPayload $payload, AiTaskPageResource $resource): AiTaskPageResource
    {
        return $resource->fromTask($this->runner->submit($payload->prompt));
    }
}
