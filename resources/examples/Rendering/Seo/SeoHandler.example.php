<?php

declare(strict_types=1);

namespace App\Application\Handler\Rendering;

use App\Application\Payload\Page\SeoPayload;
use App\Application\Resource\Page\SeoPageResource;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: SeoPayload::class, resource: SeoPageResource::class)]
final class SeoHandler implements TypedHandlerInterface
{
    public function handle(SeoPayload $payload, SeoPageResource $resource): SeoPageResource
    {
        return $resource
            ->pageTitle('Catalog — Acme Store')
            ->seoTag('description', 'Browse the current featured catalog.')
            ->seoTag('og:title', 'Catalog — Acme Store')
            ->seoTag('og:type', 'website');
    }
}
