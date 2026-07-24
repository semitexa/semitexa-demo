<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Repository;

use Semitexa\Demo\Domain\Model\DemoCategory;

interface DemoCategoryRepositoryInterface
{
    public function findById(string $id): ?DemoCategory;

    public function save(DemoCategory $entity): DemoCategory;

    public function findBySlug(string $slug): ?DemoCategory;

    /** @return list<DemoCategory> */
    public function findAllOrdered(): array;
}
