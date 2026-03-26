<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoOrderResource;
use Semitexa\Orm\Repository\AbstractRepository;

#[AsService]
final class DemoOrderRepository extends AbstractRepository
{
    protected function getResourceClass(): string
    {
        return DemoOrderResource::class;
    }

    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        return $this->select()
            ->where('tenant_id', '=', $tenantId)
            ->limit($limit)
            ->fetchAll();
    }

    public function findByUser(string $userId): array
    {
        return $this->select()
            ->where('user_id', '=', $userId)
            ->orderBy('created_at', 'DESC')
            ->fetchAll();
    }

    public function findByStatus(string $status): array
    {
        return $this->select()
            ->where('status', '=', $status)
            ->fetchAll();
    }
}
