<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Authorization\Attribute\RequiresPermission;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[RequiresPermission('products.read')]
#[AsPayload(
    path: '/demo/events/ledger',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
final class LedgerDemoPayload
{
    private ?string $action = null;
    private ?string $nonce = null;

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): void
    {
        $action = $action !== null ? trim($action) : null;
        $this->action = $action !== '' ? $action : null;
    }

    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function setNonce(?string $nonce): void
    {
        $nonce = $nonce !== null ? trim($nonce) : null;
        $this->nonce = $nonce !== '' ? $nonce : null;
    }
}
