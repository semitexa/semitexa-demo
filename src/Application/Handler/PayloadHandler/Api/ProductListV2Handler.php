<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListV2Payload;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;
use Semitexa\Demo\Application\Service\DemoApiPresenter;

#[AsPayloadHandler(payload: ProductListV2Payload::class, resource: DemoApiResponse::class)]
final class ProductListV2Handler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    public function handle(ProductListV2Payload $payload, DemoApiResponse $resource): DemoApiResponse
    {
        $request = $payload->getHttpRequest() ?? new Request('GET', '/demo/api/v2/products', [], [], [], [], []);

        return $resource->withJsonPayload(
            $this->apiPresenter->buildCollection(request: $request, query: $payload->getQ()),
            $this->apiPresenter->getContentType($request),
        );
    }
}
