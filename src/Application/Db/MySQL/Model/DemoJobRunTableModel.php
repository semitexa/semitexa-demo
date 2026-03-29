<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\PrimaryKey;
use Semitexa\Orm\Metadata\HasColumnReferences;
use Semitexa\Orm\Metadata\HasRelationReferences;

#[FromTable(name: 'demo_job_runs')]
final readonly class DemoJobRunTableModel
{
    use HasColumnReferences;
    use HasRelationReferences;

    public function __construct(
        #[PrimaryKey(strategy: 'uuid')]
        #[Column(type: MySqlType::Binary, length: 16)]
        public string $id,
        #[Column(type: MySqlType::Varchar, length: 64)]
        public string $job_type,
        #[Column(type: MySqlType::Char, length: 36, nullable: true)]
        public ?string $scheduler_run_id,
        #[Column(type: MySqlType::Varchar, length: 32)]
        public string $status,
        #[Column(type: MySqlType::Int)]
        public int $progress_percent,
        #[Column(type: MySqlType::Varchar, length: 255, nullable: true)]
        public ?string $progress_message,
        #[Column(type: MySqlType::Json, nullable: true)]
        public ?string $result_payload,
        #[Column(type: MySqlType::Int)]
        public int $attempt_number,
        #[Column(type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $created_at,
        #[Column(type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $updated_at,
    ) {}
}
