<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Core\Http\Response\GenericResponse;

final class DemoApiResponse extends GenericResponse implements ResourceInterface
{
    public function withJsonPayload(array $payload, string $contentType = 'application/json'): self
    {
        $this->setContent(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
        );
        $this->setHeader('Content-Type', $contentType);

        return $this;
    }
}
