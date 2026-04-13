<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\Filterable;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\Index;
use Semitexa\Orm\Attribute\PrimaryKey;
use Semitexa\Orm\Metadata\HasColumnReferences;
use Semitexa\Orm\Metadata\HasRelationReferences;

#[FromTable(name: 'demo_job_runs')]
#[Index(columns: ['job_type', 'status'], name: 'idx_demo_job_runs_type_status')]
#[Index(columns: ['scheduler_run_id'], name: 'idx_demo_job_runs_scheduler')]
final readonly class DemoJobRunResource
{
    use HasColumnReferences;
    use HasRelationReferences;

    public function __construct(
        #[PrimaryKey(strategy: 'uuid')]
        #[Column(type: MySqlType::Binary, length: 16)]
        public string $id,
        #[Filterable]
        #[Column(name: 'job_type', type: MySqlType::Varchar, length: 64)]
        public string $jobType,
        #[Column(name: 'scheduler_run_id', type: MySqlType::Char, length: 36, nullable: true)]
        public ?string $schedulerRunId,
        #[Filterable]
        #[Column(type: MySqlType::Varchar, length: 32)]
        public string $status,
        #[Column(name: 'progress_percent', type: MySqlType::Int)]
        public int $progressPercent,
        #[Column(name: 'progress_message', type: MySqlType::Varchar, length: 255, nullable: true)]
        public ?string $progressMessage,
        #[Column(name: 'result_payload', type: MySqlType::Json, nullable: true)]
        public ?string $resultPayload,
        #[Column(name: 'attempt_number', type: MySqlType::Int)]
        public int $attemptNumber,
        #[Column(name: 'created_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $createdAt,
        #[Column(name: 'updated_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $updatedAt,
    ) {}
}
