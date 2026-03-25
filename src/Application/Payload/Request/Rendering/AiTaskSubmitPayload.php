<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/reactive-ai/submit',
    methods: ['POST'],
    responseWith: DemoFeatureResource::class,
)]
class AiTaskSubmitPayload
{
    protected ?string $inputText = null;

    public function getInputText(): ?string { return $this->inputText; }
    public function setInputText(?string $text): void { $this->inputText = $text; }
}
