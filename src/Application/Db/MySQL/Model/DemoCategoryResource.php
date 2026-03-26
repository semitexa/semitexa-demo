<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Adapter\MySqlType;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\HasMany;
use Semitexa\Orm\Attribute\Index;
use Semitexa\Orm\Trait\HasTimestamps;
use Semitexa\Orm\Trait\HasUuidV7;

#[FromTable(name: 'demo_categories')]
#[Index(columns: ['slug'], unique: true, name: 'uniq_demo_categories_slug')]
class DemoCategoryResource
{
    use HasUuidV7;
    use HasTimestamps;

    #[Column(type: MySqlType::Varchar, length: 255)]
    public string $name = '';

    #[Column(type: MySqlType::Varchar, length: 255)]
    public string $slug = '';

    #[Column(type: MySqlType::Text, nullable: true)]
    public ?string $description = null;

    #[HasMany(target: DemoProductResource::class, foreignKey: 'category_id')]
    /** @var list<DemoProductResource> */
    public array $products = [];
}
