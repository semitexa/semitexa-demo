<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoCategoryResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoReviewResource;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoCategoryRepository;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Payload\Request\Data\RelationsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: RelationsPayload::class, resource: DemoFeatureResource::class)]
final class RelationsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCategoryRepository $categoryRepository;

    #[InjectAsReadonly]
    protected DemoProductRepository $productRepository;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(RelationsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $categories = $this->categoryRepository->findAll(3);
        $products = $this->productRepository->findAll(3);

        $categoryRows = '';
        foreach ($categories as $category) {
            /** @var DemoCategoryResource $category */
            $productCount = count($category->products);
            $categoryRows .= sprintf(
                '<tr><td>%s</td><td>%d products</td></tr>',
                htmlspecialchars($category->name),
                $productCount,
            );
        }

        $productRows = '';
        foreach ($products as $product) {
            /** @var DemoProductResource $product */
            $reviewCount = count($product->reviews);
            $categoryName = $product->category?->name ?? '—';
            $productRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%d reviews</td></tr>',
                htmlspecialchars($product->name),
                htmlspecialchars($categoryName),
                $reviewCount,
            );
        }

        $resultPreview = '<div class="result-preview">'
            . '<h4>Categories → Products (HasMany)</h4>'
            . '<table class="data-table"><thead><tr><th>Category</th><th>Relation</th></tr></thead>'
            . '<tbody>' . ($categoryRows ?: '<tr><td colspan="2">Seed data first.</td></tr>') . '</tbody>'
            . '</table>'
            . '<h4>Products → Category + Reviews (BelongsTo + HasMany)</h4>'
            . '<table class="data-table"><thead><tr><th>Product</th><th>Category</th><th>Reviews</th></tr></thead>'
            . '<tbody>' . ($productRows ?: '<tr><td colspan="3">Seed data first.</td></tr>') . '</tbody>'
            . '</table>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('data', 'relations') ?? [];

        $sourceCode = [
            'Product Model' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
            'Category Model' => $this->sourceCodeReader->readClassSource(DemoCategoryResource::class),
        ];

        return $resource
            ->pageTitle('Relations — Semitexa Demo')
            ->withSection('data')
            ->withSlug('relations')
            ->withTitle('Relations')
            ->withSummary('Declare associations with attributes — eager loading, N+1 prevention, and nested reads.')
            ->withEntryLine('Declare associations with attributes — eager loading, N+1 prevention, and nested reads.')
            ->withHighlights(['#[HasMany]', '#[BelongsTo]', 'CascadeSaver', 'CascadeDeleter', 'eager loading'])
            ->withLearnMoreLabel('See the relation attributes →')
            ->withDeepDiveLabel('How eager loading works →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
