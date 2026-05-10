<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
    path: '/demo/rendering/reactive-ai/submit',
    methods: ['GET', 'POST'],
)]
class AiTaskSubmitPayload
{
    protected ?string $inputText = null;

    public function getInputText(): ?string { return $this->inputText; }
    public function setInputText(?string $text): void { $this->inputText = $text; }
}
