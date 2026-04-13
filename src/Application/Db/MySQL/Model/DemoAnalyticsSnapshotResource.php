<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\Filterable;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\Index;
use Semitexa\Orm\Attribute\PrimaryKey;
use Semitexa\Orm\Attribute\TenantScoped;
use Semitexa\Orm\Metadata\HasColumnReferences;
use Semitexa\Orm\Metadata\HasRelationReferences;

#[FromTable(name: 'demo_analytics_snapshots')]
#[TenantScoped(strategy: 'column', column: 'tenant_id')]
#[Index(columns: ['tenant_id', 'metric_type', 'period_start'], name: 'idx_demo_analytics_tenant_metric_period')]
final readonly class DemoAnalyticsSnapshotResource
{
    use HasColumnReferences;
    use HasRelationReferences;

    public function __construct(
        #[PrimaryKey(strategy: 'uuid')]
        #[Column(type: MySqlType::Binary, length: 16)]
        public string $id,
        #[Column(name: 'tenant_id', type: MySqlType::Varchar, length: 64, nullable: true)]
        public ?string $tenantId,
        #[Filterable]
        #[Column(name: 'metric_type', type: MySqlType::Varchar, length: 64)]
        public string $metricType,
        #[Column(type: MySqlType::Decimal, precision: 12, scale: 4)]
        public string $value,
        #[Column(name: 'period_start', type: MySqlType::Datetime)]
        public ?\DateTimeImmutable $periodStart,
        #[Column(name: 'period_end', type: MySqlType::Datetime)]
        public ?\DateTimeImmutable $periodEnd,
        #[Column(name: 'created_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $createdAt,
        #[Column(name: 'updated_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $updatedAt,
    ) {}
}
