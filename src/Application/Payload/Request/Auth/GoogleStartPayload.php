<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Http\Response\ResourceResponse;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/google/start',
    methods: ['GET'],
    responseWith: ResourceResponse::class,
    produces: ['application/json', 'text/html'],
)]
class GoogleStartPayload
{
    protected ?string $returnTo = null;

    public function getReturnTo(): ?string
    {
        return $this->returnTo;
    }

    public function setReturnTo(?string $returnTo): void
    {
        $this->returnTo = $returnTo !== null && trim($returnTo) !== '' ? trim($returnTo) : null;
    }
}
