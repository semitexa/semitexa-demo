<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Payload\Request\Api\ProductSchemaPayload;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;
use Semitexa\Demo\Application\Service\DemoApiPresenter;

#[AsPayloadHandler(payload: ProductSchemaPayload::class, resource: DemoApiResponse::class)]
final class ProductSchemaHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    public function handle(ProductSchemaPayload $payload, DemoApiResponse $resource): DemoApiResponse
    {
        $request = $payload->getHttpRequest() ?? new Request('GET', '/demo/api/v1/products/_schema', [], [], [], [], []);
        $accept = strtolower($request->getHeader('Accept') ?? '');
        $contentType = str_contains($accept, 'application/schema+json')
            ? 'application/schema+json'
            : 'application/json';

        return $resource->withJsonPayload($this->apiPresenter->buildProductSchema(), $contentType);
    }
}
