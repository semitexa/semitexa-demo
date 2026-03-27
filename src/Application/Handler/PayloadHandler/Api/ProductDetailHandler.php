<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Exception\DemoApiNotFoundException;
use Semitexa\Demo\Application\Payload\Request\Api\ProductDetailPayload;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;
use Semitexa\Demo\Application\Service\DemoApiPresenter;

#[AsPayloadHandler(payload: ProductDetailPayload::class, resource: DemoApiResponse::class)]
final class ProductDetailHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    public function handle(ProductDetailPayload $payload, DemoApiResponse $resource): DemoApiResponse
    {
        $request = $payload->getHttpRequest() ?? new Request('GET', '/demo/api/v1/products/' . $payload->getSlug(), [], [], [], [], []);
        $body = $this->apiPresenter->buildDetail(
            request: $request,
            slug: $payload->getSlug(),
            fields: $payload->getFields(),
            expand: $payload->getExpand(),
            profile: $payload->getProfile(),
            format: $payload->getFormat(),
        );

        if ($body === null) {
            throw new DemoApiNotFoundException('Demo API product', $payload->getSlug());
        }

        return $resource->withJsonPayload($body, $this->apiPresenter->getContentType($request, $payload->getFormat()));
    }
}
