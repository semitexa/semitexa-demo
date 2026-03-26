<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\Filterable;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\Index;
use Semitexa\Orm\Attribute\TenantScoped;
use Semitexa\Orm\Trait\HasTimestamps;
use Semitexa\Orm\Trait\HasUuidV7;

#[FromTable(name: 'demo_analytics_snapshots')]
#[TenantScoped(strategy: 'same_storage')]
#[Index(columns: ['tenant_id', 'metric_type', 'period_start'], name: 'idx_demo_analytics_tenant_metric_period')]
class DemoAnalyticsSnapshotResource
{
    use HasUuidV7;
    use HasTimestamps;

    #[Column(type: MySqlType::Varchar, length: 64, nullable: true)]
    public ?string $tenant_id = null;

    #[Filterable]
    #[Column(type: MySqlType::Varchar, length: 64)]
    public string $metric_type = '';

    #[Column(type: MySqlType::Decimal, precision: 12, scale: 4)]
    public float $value = 0.0;

    #[Column(type: MySqlType::Datetime)]
    public ?\DateTimeImmutable $period_start = null;

    #[Column(type: MySqlType::Datetime)]
    public ?\DateTimeImmutable $period_end = null;
}
