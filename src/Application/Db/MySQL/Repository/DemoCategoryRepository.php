<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoCategoryResource;
use Semitexa\Orm\Repository\AbstractRepository;

#[AsService]
final class DemoCategoryRepository extends AbstractRepository
{
    protected function getResourceClass(): string
    {
        return DemoCategoryResource::class;
    }

    public function findBySlug(string $slug): ?DemoCategoryResource
    {
        /** @var DemoCategoryResource|null */
        return $this->select()
            ->where('slug', '=', $slug)
            ->fetchOne();
    }

    public function findAllOrdered(): array
    {
        return $this->select()
            ->orderBy('name', 'ASC')
            ->fetchAll();
    }
}
