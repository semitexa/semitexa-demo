<?php

declare(strict_types=1);

namespace App\Insights\Application\Handler;

use App\Catalog\Application\Payload\Request\SearchPayload;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: SearchPayload::class, resource: SearchAuditResource::class)]
final class SearchAuditHandler implements TypedHandlerInterface
{
    public function handle(SearchPayload $payload, SearchAuditResource $resource): SearchAuditResource
    {
        return $resource
            ->withQuery($payload->getQuery())
            ->withCampaign($payload->getCampaign())
            ->withPreview($payload->isPreview());
    }
}
