<?php

declare(strict_types=1);

namespace Examples\Orm\QueryBuilder;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\TableModelQuery;
use Semitexa\Orm\Repository\DomainRepository;

final class ProductQueryBuilder
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function new(): TableModelQuery
    {
        return $this->repository()->query();
    }

    public function map(TableModelQuery $query): array
    {
        /** @var list<DemoProduct> */
        return $query->fetchAllAs(DemoProduct::class, $this->orm()->getMapperRegistry());
    }

    public function one(TableModelQuery $query): ?DemoProduct
    {
        /** @var DemoProduct|null */
        return $query->fetchOneAs(DemoProduct::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(
            DemoProductResource::class,
            DemoProduct::class,
        );
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }
}
