<?php

declare(strict_types=1);

namespace App\Application\Db\Model;

use Semitexa\Orm\Attribute\BelongsTo;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;
use Semitexa\Orm\Attribute\HasMany;

#[FromTable(name: 'products')]
final class ProductResource
{
    #[Column(type: 'uuid')]
    public string $id = '';

    #[Column(type: 'varchar', length: 255)]
    public string $name = '';

    #[Column(type: 'uuid', nullable: true)]
    public ?string $category_id = null;

    #[BelongsTo(target: CategoryResource::class, foreignKey: 'category_id')]
    public ?CategoryResource $category = null;

    /** @var list<ReviewResource> */
    #[HasMany(target: ReviewResource::class, foreignKey: 'product_id')]
    public array $reviews = [];
}
