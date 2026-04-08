<?php

declare(strict_types=1);

namespace App\Application\Db\Model;

use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\HasMany;

#[FromTable(name: 'categories')]
final class CategoryResource
{
    #[Column(type: 'uuid')]
    public string $id = '';

    #[Column(type: 'varchar', length: 255)]
    public string $name = '';

    /** @var list<ProductResource> */
    #[HasMany(target: ProductResource::class, foreignKey: 'category_id')]
    public array $products = [];
}
