<?php

declare(strict_types=1);

namespace Examples\Orm\QueryBuilder;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;

#[AsRepository]
final class ProductAdminRepository
{
    #[InjectAsReadonly]
    protected ProductQueryBuilder $queryBuilder;

    public function findDraftsForTenant(string $tenantId): array
    {
        return $this->queryBuilder
            ->map(
                $this->queryBuilder
                    ->new()
                    ->forTenant($tenantId)
                    ->where(DemoProductResource::column('status'), Operator::Equals, 'draft')
                    ->includeSoftDeleted()
                    ->orderBy(DemoProductResource::column('updated_at'), Direction::Desc)
                    ->limit(50),
            );
    }

    public function findWithoutCategory(string $tenantId): array
    {
        return $this->queryBuilder
            ->map(
                $this->queryBuilder
                    ->new()
                    ->forTenant($tenantId)
                    ->whereNull(DemoProductResource::column('category_id'))
                    ->whereNotNull(DemoProductResource::column('created_at'))
                    ->orderBy(DemoProductResource::column('name'), Direction::Asc),
            );
    }

    public function findMostExpensiveForTenant(string $tenantId): ?DemoProduct
    {
        return $this->queryBuilder
            ->one(
                $this->queryBuilder
                    ->new()
                    ->forTenant($tenantId)
                    ->where(DemoProductResource::column('status'), Operator::Equals, 'active')
                    ->orderBy(DemoProductResource::column('price'), Direction::Desc)
                    ->limit(1),
            );
    }
}
