<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Data\OrmCrudPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;

#[AsPayloadHandler(payload: OrmCrudPayload::class, resource: DemoFeatureResource::class)]
final class OrmCrudHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoProductRepositoryInterface $productRepository;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(OrmCrudPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $this->applyMutation($payload);
        $products = $this->productRepository->findByTenant('demo', 8);

        $spec = new FeatureSpec(
            section: 'data',
            slug: 'products',
            entryLine: 'Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.',
            learnMoreLabel: 'See the model & repository →',
            deepDiveLabel: 'How the ORM maps resources →',
            relatedSlugs: [],
            fallbackTitle: 'ORM CRUD',
            fallbackSummary: 'Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.',
            fallbackHighlights: ['#[FromTable]', '#[Column]', 'HasUuidV7', 'HasTimestamps', 'SoftDeletes', 'DomainRepository'],
            explanation: $this->explanationProvider->getExplanation('data', 'products') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Model' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
                // NB: unqualified DemoProductRepository::class resolves to this namespace and
                //     is intentionally unimported — matches baseline behavior where this key
                //     resolves to an empty string via readClassSource's missing-class guard.
                'Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Repository Snapshot',
                'title' => 'Current product set',
                'summary' => 'A small working dataset backed by the ORM. Create and delete actions update this view.',
                'stats' => [
                    ['value' => (string) count($products), 'label' => 'Visible rows'],
                    ['value' => 'demo', 'label' => 'Tenant scope'],
                ],
                'columns' => ['Name', 'Price', 'Status'],
                'rows' => array_map(
                    static fn (DemoProduct $product): array => [
                        ['text' => $product->getName()],
                        ['text' => '$' . number_format((float) $product->getPrice(), 2)],
                        ['text' => $product->getStatus(), 'variant' => $product->getStatus()],
                    ],
                    $products,
                ),
                'emptyMessage' => 'No products yet — seed the data first.',
            ]);
    }

    private function applyMutation(OrmCrudPayload $payload): void
    {
        if ($payload->getHttpRequest()?->isPost() !== true) {
            return;
        }

        $action = $payload->getAction();

        if ($action === 'create' && $payload->getName() !== null) {
            $this->createProduct($payload);
            return;
        }

        if ($action === 'delete' && $payload->getProductId() !== null) {
            $this->deleteProduct($payload->getProductId());
        }
    }

    private function createProduct(OrmCrudPayload $payload): void
    {
        $price = $payload->getPrice() ?? 9.99;
        if ($price < 0 || $price > 99999.99) {
            $price = 9.99;
        }

        $product = new DemoProduct();
        $product->setName($payload->getName());
        $product->setPrice(number_format($price, 2, '.', ''));
        $product->setStatus('active');
        $product->setTenantId('demo');

        $this->productRepository->save($product);
    }

    private function deleteProduct(string $productId): void
    {
        $product = $this->productRepository->findById($productId);
        if ($product !== null && $product->getTenantId() === 'demo') {
            $this->productRepository->delete($product);
        }
    }
}
