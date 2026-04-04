<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListPayload;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;
use Semitexa\Demo\Application\Service\DemoApiPresenter;

#[AsPayloadHandler(payload: ProductListPayload::class, resource: DemoApiResponse::class)]
final class ProductListHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    public function handle(ProductListPayload $payload, DemoApiResponse $resource): DemoApiResponse
    {
        $request = $payload->getHttpRequest() ?? new Request('GET', '/demo/api/v1/products', [], [], [], [], []);
        $contentType = $this->apiPresenter->getContentType($request, $payload->getFormat());
        $body = $this->apiPresenter->buildCollection(
            request: $request,
            query: $payload->getQ(),
            status: $payload->getStatus(),
            page: $payload->getPage(),
            limit: $payload->getLimit(),
            fields: $payload->getFields(),
            expand: $payload->getExpand(),
            profile: $payload->getProfile(),
            format: $payload->getFormat(),
        );

        return $resource->withJsonPayload($body, $contentType);
    }
}
