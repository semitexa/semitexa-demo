<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\BelongsTo;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\Filterable;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\Index;
use Semitexa\Orm\Attribute\TenantScoped;
use Semitexa\Orm\Trait\HasTimestamps;
use Semitexa\Orm\Trait\HasUuidV7;

#[FromTable(name: 'demo_reviews')]
#[TenantScoped(strategy: 'same_storage')]
#[Index(columns: ['product_id'], name: 'idx_demo_reviews_product')]
#[Index(columns: ['tenant_id', 'user_id'], name: 'idx_demo_reviews_tenant_user')]
class DemoReviewResource
{
    use HasUuidV7;
    use HasTimestamps;

    #[Column(type: MySqlType::Varchar, length: 64, nullable: true)]
    public ?string $tenant_id = null;

    #[Column(type: MySqlType::Char, length: 36)]
    public string $product_id = '';

    #[Column(type: MySqlType::Char, length: 36)]
    public string $user_id = '';

    #[Filterable]
    #[Column(type: MySqlType::Int)]
    public int $rating = 0;

    #[Column(type: MySqlType::Text, nullable: true)]
    public ?string $body = null;

    #[BelongsTo(target: DemoProductResource::class, foreignKey: 'product_id')]
    public ?DemoProductResource $product = null;
}
