<?php

declare(strict_types=1);

namespace Examples\Orm\QueryBuilder;

use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductTableModel;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;

#[AsRepository]
final class ProductReadRepository
{
    #[InjectAsReadonly]
    protected ProductQueryBuilder $queryBuilder;

    public function findActivePage(int $limit = 12, int $offset = 0): array
    {
        return $this->queryBuilder
            ->map(
                $this->queryBuilder
                    ->new()
                    ->where(DemoProductTableModel::column('status'), Operator::Equals, 'active')
                    ->whereNull(DemoProductTableModel::column('deleted_at'))
                    ->orderBy(DemoProductTableModel::column('created_at'), Direction::Desc)
                    ->limit($limit)
                    ->offset($offset),
            );
    }

    public function searchCatalog(string $term, ?float $maxPrice = null): array
    {
        $query = $this->queryBuilder
            ->new()
            ->where(DemoProductTableModel::column('name'), Operator::Like, '%' . $term . '%')
            ->where(DemoProductTableModel::column('status'), Operator::Equals, 'active')
            ->orderBy(DemoProductTableModel::column('name'), Direction::Asc)
            ->limit(20);

        if ($maxPrice !== null) {
            $query->where(DemoProductTableModel::column('price'), Operator::LessThanOrEquals, $maxPrice);
        }

        return $this->queryBuilder->map($query);
    }

    public function findOneBySlug(string $slug): ?DemoProductResource
    {
        return $this->queryBuilder
            ->one(
                $this->queryBuilder
                    ->new()
                    ->where(DemoProductTableModel::column('slug'), Operator::Equals, $slug)
                    ->where(DemoProductTableModel::column('status'), Operator::Equals, 'active'),
            );
    }
}
