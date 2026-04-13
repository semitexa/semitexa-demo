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

#[FromTable(name: 'demo_products')]
#[TenantScoped(strategy: 'column', column: 'tenantId')]
#[Index(columns: ['tenant_id', 'status'], name: 'idx_demo_products_tenant_status')]
#[Index(columns: ['category_id'], name: 'idx_demo_products_category')]
final readonly class DemoProductResource
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
        #[Column(type: MySqlType::Varchar, length: 255)]
        public string $name,
        #[Column(type: MySqlType::Text, nullable: true)]
        public ?string $description,
        #[Filterable]
        #[Column(type: MySqlType::Decimal, precision: 10, scale: 2)]
        public string $price,
        #[Filterable]
        #[Column(type: MySqlType::Varchar, length: 32)]
        public string $status,
        #[Column(name: 'category_id', type: MySqlType::Binary, length: 16, nullable: true)]
        public ?string $categoryId,
        #[Column(name: 'deleted_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $deletedAt,
        #[Column(name: 'created_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $createdAt,
        #[Column(name: 'updated_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $updatedAt,
    ) {}
}
