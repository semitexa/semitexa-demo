<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/google',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
class GoogleAuthPayload
{
    protected ?string $returnTo = null;
    protected ?string $googleError = null;
    protected bool $localTestBypass = false;

    public function getReturnTo(): ?string
    {
        return $this->returnTo;
    }

    public function setReturnTo(?string $returnTo): void
    {
        $this->returnTo = $returnTo !== null && trim($returnTo) !== '' ? trim($returnTo) : null;
    }

    public function getGoogleError(): ?string
    {
        return $this->googleError;
    }

    public function setGoogleError(?string $googleError): void
    {
        $this->googleError = $googleError !== null && trim($googleError) !== '' ? trim($googleError) : null;
    }

    public function isLocalTestBypass(): bool
    {
        return $this->localTestBypass;
    }

    public function setLocalTestBypass(bool|int|string|null $localTestBypass): void
    {
        $this->localTestBypass = filter_var($localTestBypass, FILTER_VALIDATE_BOOL);
    }
}
