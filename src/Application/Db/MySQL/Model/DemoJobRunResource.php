<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\Filterable;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\Index;
use Semitexa\Orm\Trait\HasTimestamps;
use Semitexa\Orm\Trait\HasUuidV7;

#[FromTable(name: 'demo_job_runs')]
#[Index(columns: ['job_type', 'status'], name: 'idx_demo_job_runs_type_status')]
#[Index(columns: ['scheduler_run_id'], name: 'idx_demo_job_runs_scheduler')]
class DemoJobRunResource
{
    use HasUuidV7;
    use HasTimestamps;

    #[Filterable]
    #[Column(type: MySqlType::Varchar, length: 64)]
    public string $job_type = '';

    #[Column(type: MySqlType::Char, length: 36, nullable: true)]
    public ?string $scheduler_run_id = null;

    #[Filterable]
    #[Column(type: MySqlType::Varchar, length: 32)]
    public string $status = 'pending';

    #[Column(type: MySqlType::Int)]
    public int $progress_percent = 0;

    #[Column(type: MySqlType::Varchar, length: 255, nullable: true)]
    public ?string $progress_message = null;

    #[Column(type: MySqlType::Json, nullable: true)]
    public ?string $result_payload = null;

    #[Column(type: MySqlType::Int)]
    public int $attempt_number = 1;
}
