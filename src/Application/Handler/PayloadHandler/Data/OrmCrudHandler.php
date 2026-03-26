<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Payload\Request\Data\OrmCrudPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: OrmCrudPayload::class, resource: DemoFeatureResource::class)]
final class OrmCrudHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoProductRepository $productRepository;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(OrmCrudPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $action = $payload->getAction();
        $isMutationRequest = $payload->getHttpRequest()?->isPost() === true;

        if ($isMutationRequest && $action === 'create' && $payload->getName() !== null) {
            $price = $payload->getPrice() ?? 9.99;
            if ($price < 0 || $price > 99999.99) {
                $price = 9.99;
            }

            $product = new DemoProductResource();
            $product->name = $payload->getName();
            $product->price = number_format($price, 2, '.', '');
            $product->status = 'active';
            $product->tenant_id = 'demo';
            $this->productRepository->save($product);
        } elseif ($isMutationRequest && $action === 'delete' && $payload->getProductId() !== null) {
            $product = $this->productRepository->findById($payload->getProductId());
            if ($product !== null && $product->tenant_id === 'demo') {
                $this->productRepository->delete($product);
            }
        }

        $products = $this->productRepository->findByTenant('demo', 8);

        $rows = '';
        foreach ($products as $product) {
            /** @var DemoProductResource $product */
            $rows .= sprintf(
                '<tr><td>%s</td><td>$%.2f</td><td><span class="badge badge--%s">%s</span></td></tr>',
                htmlspecialchars($product->name),
                $product->price,
                htmlspecialchars($product->status),
                htmlspecialchars($product->status),
            );
        }

        $resultPreview = '<div class="result-preview">'
            . '<table class="data-table"><thead><tr><th>Name</th><th>Price</th><th>Status</th></tr></thead>'
            . '<tbody>' . ($rows ?: '<tr><td colspan="3">No products yet — seed the data first.</td></tr>') . '</tbody>'
            . '</table></div>';

        $explanation = $this->explanationProvider->getExplanation('data', 'products') ?? [];

        $sourceCode = [
            'Model' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
            'Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('ORM CRUD — Semitexa Demo')
            ->withSection('data')
            ->withSlug('products')
            ->withTitle('ORM CRUD')
            ->withSummary('Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.')
            ->withEntryLine('Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.')
            ->withHighlights(['#[FromTable]', '#[Column]', 'HasUuidV7', 'HasTimestamps', 'SoftDeletes', 'AbstractRepository'])
            ->withLearnMoreLabel('See the model & repository →')
            ->withDeepDiveLabel('How the ORM maps resources →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
