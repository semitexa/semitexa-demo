<?php

declare(strict_types=1);

namespace App\Application\Db\Repository;

use App\Application\Db\Model\ProductResource;
use App\Domain\Catalog\ProductReadRepositoryInterface;

final class ProductRepository implements ProductReadRepositoryInterface
{
    /**
     * @return list<ProductResource>
     */
    public function findForCatalog(object $criteria): array
    {
        return [];
    }
}
