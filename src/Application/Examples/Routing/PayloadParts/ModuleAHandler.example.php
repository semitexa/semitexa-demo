<?php

declare(strict_types=1);

namespace App\Catalog\Application\Handler;

use App\Catalog\Application\Payload\Request\SearchPayload;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: SearchPayload::class, resource: SearchPageResource::class)]
final class SearchPageHandler implements TypedHandlerInterface
{
    public function handle(SearchPayload $payload, SearchPageResource $resource): SearchPageResource
    {
        return $resource
            ->withQuery($payload->getQuery())
            ->withCampaign($payload->getCampaign())
            ->withPreview($payload->isPreview());
    }
}
