<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Search;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Demo\Application\Payload\Request\Search\DocsSearchIndexPayload;
use Semitexa\Demo\Application\Service\DocsSearchIndex;

#[AsPayloadHandler(payload: DocsSearchIndexPayload::class, resource: ResourceResponse::class)]
final class DocsSearchIndexHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DocsSearchIndex $index;

    public function handle(DocsSearchIndexPayload $payload, ResourceResponse $resource): ResourceResponse
    {
        return $resource
            ->setContent(json_encode(['items' => $this->index->items()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            // The catalog changes only with a deploy; the dialog may reuse it for an hour.
            ->setHeader('Cache-Control', 'public, max-age=3600')
            // An endpoint for the dialog, not a page to rank.
            ->setHeader('X-Robots-Tag', 'noindex');
    }
}
