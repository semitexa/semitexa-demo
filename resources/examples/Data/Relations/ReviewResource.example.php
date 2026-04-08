<?php

declare(strict_types=1);

namespace App\Application\Db\Model;

use Semitexa\Orm\Attribute\BelongsTo;
use Semitexa\Orm\Attribute\Column;
use Semitexa\Orm\Attribute\FromTable;

#[FromTable(name: 'reviews')]
final class ReviewResource
{
    #[Column(type: 'uuid')]
    public string $id = '';

    #[Column(type: 'uuid')]
    public string $product_id = '';

    #[Column(type: 'int')]
    public ?int $rating = null;

    #[BelongsTo(target: ProductResource::class, foreignKey: 'product_id')]
    public ?ProductResource $product = null;
}
