<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Authorization\Attribute\RequiresPermission;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[RequiresPermission('products.read')]
#[AsPayload(
    path: '/demo/events/ledger',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'events',
    title: 'Ledger Demo',
    slug: 'ledger',
    summary: 'Dispatch a protected demo event and inspect only the persisted demo ledger rows through a safe read-only view.',
    order: 2,
    highlights: ['#[Propagated]', '#[RequiresPermission]', 'typed session nonce', 'SQLite read-only view'],
    entryLine: 'The demo app can append propagated events into Semitexa Ledger, but the inspection surface stays authenticated, filtered, and read-only.',
    learnMoreLabel: 'See the protected ledger flow →',
    deepDiveLabel: 'How the safe ledger inspector works →',
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
