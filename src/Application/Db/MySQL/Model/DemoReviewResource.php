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

#[FromTable(name: 'demo_reviews')]
#[TenantScoped(strategy: 'column', column: 'tenant_id')]
#[Index(columns: ['product_id'], name: 'idx_demo_reviews_product')]
#[Index(columns: ['tenant_id', 'user_id'], name: 'idx_demo_reviews_tenant_user')]
final readonly class DemoReviewResource
{
    use HasColumnReferences;
    use HasRelationReferences;

    public function __construct(
        #[PrimaryKey(strategy: 'uuid')]
        #[Column(type: MySqlType::Binary, length: 16)]
        public string $id,
        #[Column(name: 'tenant_id', type: MySqlType::Varchar, length: 64, nullable: true)]
        public ?string $tenantId,
        #[Column(name: 'product_id', type: MySqlType::Binary, length: 16)]
        public string $productId,
        #[Column(name: 'user_id', type: MySqlType::Char, length: 36)]
        public string $userId,
        #[Filterable]
        #[Column(type: MySqlType::Int, nullable: true)]
        public ?int $rating,
        #[Column(type: MySqlType::Text, nullable: true)]
        public ?string $body,
        #[Column(name: 'created_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $createdAt,
        #[Column(name: 'updated_at', type: MySqlType::Datetime, nullable: true)]
        public ?\DateTimeImmutable $updatedAt,
    ) {}
}
