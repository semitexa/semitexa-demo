<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\Filterable;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\Index;
use Semitexa\Orm\Attribute\TenantScoped;
use Semitexa\Orm\Contract\FilterableResourceInterface;
use Semitexa\Orm\Trait\FilterableTrait;
use Semitexa\Orm\Trait\HasTimestamps;
use Semitexa\Orm\Trait\HasUuidV7;

/**
 * Workflow states: pending → confirmed → shipped → delivered (+ cancel from any state).
 */
#[FromTable(name: 'demo_orders')]
#[TenantScoped(strategy: 'same_storage')]
#[Index(columns: ['tenant_id', 'status'], name: 'idx_demo_orders_tenant_status')]
#[Index(columns: ['user_id'], name: 'idx_demo_orders_user')]
class DemoOrderResource implements FilterableResourceInterface
{
    use HasUuidV7;
    use HasTimestamps;
    use FilterableTrait;

    #[Column(type: MySqlType::Varchar, length: 64, nullable: true)]
    public ?string $tenant_id = null;

    #[Column(type: MySqlType::Char, length: 36)]
    public string $user_id = '';

    #[Filterable]
    #[Column(type: MySqlType::Varchar, length: 32)]
    public string $status = 'pending';

    #[Filterable]
    #[Column(type: MySqlType::Decimal, precision: 10, scale: 2)]
    public float $total_amount = 0.0;
}
