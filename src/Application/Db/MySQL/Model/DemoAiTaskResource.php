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

#[FromTable(name: 'demo_ai_tasks')]
#[TenantScoped(strategy: 'same_storage')]
#[Index(columns: ['tenant_id', 'status'], name: 'idx_demo_ai_tasks_tenant_status')]
class DemoAiTaskResource
{
    use HasUuidV7;
    use HasTimestamps;

    #[Column(type: MySqlType::Varchar, length: 64, nullable: true)]
    public ?string $tenant_id = null;

    #[Column(type: MySqlType::Text)]
    public string $input_text = '';

    #[Filterable]
    #[Column(type: MySqlType::Varchar, length: 32)]
    public string $status = 'pending';

    #[Column(type: MySqlType::Json, nullable: true)]
    public ?string $stages = null;

    #[Column(type: MySqlType::Json, nullable: true)]
    public ?string $stage_results = null;
}
