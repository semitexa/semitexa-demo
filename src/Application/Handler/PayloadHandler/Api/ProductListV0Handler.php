<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListV0Payload;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;
use Semitexa\Demo\Application\Service\DemoApiPresenter;

#[AsPayloadHandler(payload: ProductListV0Payload::class, resource: DemoApiResponse::class)]
final class ProductListV0Handler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    public function handle(ProductListV0Payload $payload, DemoApiResponse $resource): DemoApiResponse
    {
        $request = $payload->getHttpRequest() ?? new Request('GET', '/demo/api/v0/products', [], [], [], [], []);

        return $resource->withJsonPayload(
            $this->apiPresenter->buildCollection(request: $request, query: $payload->getQ()),
            $this->apiPresenter->getContentType($request),
        );
    }
}
