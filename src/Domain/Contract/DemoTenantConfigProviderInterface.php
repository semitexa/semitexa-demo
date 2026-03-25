<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Contract;

use Semitexa\Demo\Domain\Model\DemoTenantConfig;

interface DemoTenantConfigProviderInterface
{
    public function getConfig(string $tenantId): ?DemoTenantConfig;

    /** @return list<DemoTenantConfig> */
    public function getAllConfigs(): array;

    /** @return list<string> */
    public function getTenantIds(): array;
}
