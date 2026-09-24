<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\BlogSsr2Resource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;

#[AsPublicPayload(responseWith: BlogSsr2Resource::class, produces: ['text/html'], path: DemoBlogCatalog::SSR2_PATH, methods: ['GET'])]
final class BlogSsr2Payload
{
    protected string $delivery = 'standard';

    public function getDelivery(): string
    {
        return $this->delivery;
    }

    public function setDelivery(string $delivery): void
    {
        $this->delivery = $delivery === 'express' ? 'express' : 'standard';
    }
}
