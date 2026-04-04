<?php

declare(strict_types=1);

namespace App\Application\Resource\Page;

use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Http\Response\HtmlResponse;

final class MerchandisingCatalogResource extends HtmlResponse implements ResourceInterface
{
    /**
     * @param list<array<string, mixed>> $products
     */
    public function fromProducts(array $products): self
    {
        return $this->with('products', $products);
    }
}
