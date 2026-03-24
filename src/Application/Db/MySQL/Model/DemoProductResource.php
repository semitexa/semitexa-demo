<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\BelongsTo;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\Filterable;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\HasMany;
use Semitexa\Orm\Attribute\Index;
use Semitexa\Orm\Attribute\TenantScoped;
use Semitexa\Orm\Contract\FilterableResourceInterface;
use Semitexa\Orm\Trait\FilterableTrait;
use Semitexa\Orm\Trait\HasTimestamps;
use Semitexa\Orm\Trait\HasUuidV7;
use Semitexa\Orm\Trait\SoftDeletes;

#[FromTable(name: 'demo_products')]
#[TenantScoped(strategy: 'same_storage')]
#[Index(columns: ['tenant_id', 'status'], name: 'idx_demo_products_tenant_status')]
#[Index(columns: ['category_id'], name: 'idx_demo_products_category')]
class DemoProductResource implements FilterableResourceInterface
{
    use HasUuidV7;
    use HasTimestamps;
    use SoftDeletes;
    use FilterableTrait;

    #[Column(type: MySqlType::Varchar, length: 64, nullable: true)]
    public ?string $tenant_id = null;

    #[Filterable]
    #[Column(type: MySqlType::Varchar, length: 255)]
    public string $name = '';

    #[Column(type: MySqlType::Text, nullable: true)]
    public ?string $description = null;

    #[Filterable]
    #[Column(type: MySqlType::Decimal, precision: 10, scale: 2)]
    public float $price = 0.0;

    #[Filterable]
    #[Column(type: MySqlType::Varchar, length: 32)]
    public string $status = 'active';

    #[Column(type: MySqlType::Char, length: 36, nullable: true)]
    public ?string $category_id = null;

    #[BelongsTo(target: DemoCategoryResource::class, foreignKey: 'category_id')]
    public ?DemoCategoryResource $category = null;

    #[HasMany(target: DemoReviewResource::class, foreignKey: 'product_id')]
    /** @var list<DemoReviewResource> */
    public array $reviews = [];
}
