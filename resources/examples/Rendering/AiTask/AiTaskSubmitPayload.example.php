<?php

declare(strict_types=1);

namespace App\Application\Payload\Rendering;

use App\Application\Resource\Page\AiTaskPageResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/ai/tasks',
    methods: ['GET', 'POST'],
    responseWith: AiTaskPageResource::class,
)]
final class AiTaskSubmitPayload
{
    public string $prompt = '';
}
