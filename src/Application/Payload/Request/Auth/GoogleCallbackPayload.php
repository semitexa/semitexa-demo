<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Http\Response\ResourceResponse;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/google/callback',
    methods: ['GET'],
    responseWith: ResourceResponse::class,
    produces: ['application/json', 'text/html'],
)]
class GoogleCallbackPayload
{
    protected ?string $code = null;
    protected ?string $state = null;
    protected ?string $error = null;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code !== null && trim($code) !== '' ? trim($code) : null;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state !== null && trim($state) !== '' ? trim($state) : null;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): void
    {
        $this->error = $error !== null && trim($error) !== '' ? trim($error) : null;
    }
}
