<?php

declare(strict_types=1);

namespace App\Application\Handler\Data;

use App\Application\Payload\Data\MerchandisingCatalogPayload;
use App\Application\Resource\Page\MerchandisingCatalogResource;
use App\Domain\Catalog\MerchandisingCatalogRepositoryInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: MerchandisingCatalogPayload::class, resource: MerchandisingCatalogResource::class)]
final class SharedTableExtensionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected MerchandisingCatalogRepositoryInterface $repository;

    public function handle(MerchandisingCatalogPayload $payload, MerchandisingCatalogResource $resource): MerchandisingCatalogResource
    {
        return $resource->fromProducts($this->repository->listCampaignProducts());
    }
}
