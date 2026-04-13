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
                    ->where(DemoProductResource::column('status'), Operator::Equals, 'active')
                    ->whereNull(DemoProductResource::column('deletedAt'))
                    ->orderBy(DemoProductResource::column('createdAt'), Direction::Desc)
                    ->limit($limit)
                    ->offset($offset),
            );
    }

    public function searchCatalog(string $term, ?float $maxPrice = null): array
    {
        $query = $this->queryBuilder
            ->new()
            ->where(DemoProductResource::column('name'), Operator::Like, '%' . $term . '%')
            ->where(DemoProductResource::column('status'), Operator::Equals, 'active')
            ->orderBy(DemoProductResource::column('name'), Direction::Asc)
            ->limit(20);

        if ($maxPrice !== null) {
            $query->where(DemoProductResource::column('price'), Operator::LessThanOrEquals, $maxPrice);
        }

        return $this->queryBuilder->map($query);
    }

    public function findOneByName(string $name): ?DemoProduct
    {
        return $this->queryBuilder
            ->one(
                $this->queryBuilder
                    ->new()
                    ->where(DemoProductResource::column('name'), Operator::Equals, $name)
                    ->where(DemoProductResource::column('status'), Operator::Equals, 'active'),
            );
    }
}
