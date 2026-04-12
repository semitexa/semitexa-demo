<?php

declare(strict_types=1);

namespace Examples\Orm\QueryBuilder;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Table\DemoProductTableModel;
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
                    ->where(DemoProductTableModel::column('status'), Operator::Equals, 'draft')
                    ->includeSoftDeleted()
                    ->orderBy(DemoProductTableModel::column('updated_at'), Direction::Desc)
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
                    ->whereNull(DemoProductTableModel::column('category_id'))
                    ->whereNotNull(DemoProductTableModel::column('created_at'))
                    ->orderBy(DemoProductTableModel::column('name'), Direction::Asc),
            );
    }

    public function findMostExpensiveForTenant(string $tenantId): ?DemoProductResource
    {
        return $this->queryBuilder
            ->one(
                $this->queryBuilder
                    ->new()
                    ->forTenant($tenantId)
                    ->where(DemoProductTableModel::column('status'), Operator::Equals, 'active')
                    ->orderBy(DemoProductTableModel::column('price'), Direction::Desc)
                    ->limit(1),
            );
    }
}
