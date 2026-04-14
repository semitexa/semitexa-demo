<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Repository;

use Semitexa\Demo\Domain\Model\DemoOrder;

interface DemoOrderRepositoryInterface
{
    public function findById(string $id): ?DemoOrder;

    public function save(DemoOrder $entity): DemoOrder;

    /** @return list<DemoOrder> */
    public function findAll(int $limit = 100): array;

    /** @return list<DemoOrder> */
    public function findByTenant(string $tenantId, int $limit = 100): array;

    /** @return list<DemoOrder> */
    public function findByUser(string $userId): array;

    /** @return list<DemoOrder> */
    public function findByStatus(string $status): array;
}
