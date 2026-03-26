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
use Semitexa\Demo\Application\Service\DemoCatalogService;
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

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

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

        $rows = [];
        foreach ($products as $product) {
            /** @var DemoProductResource $product */
            $rows[] = [
                ['text' => $product->name],
                ['text' => '$' . number_format((float) $product->price, 2)],
                ['text' => (string) $product->status, 'variant' => (string) $product->status],
            ];
        }

        $explanation = $this->explanationProvider->getExplanation('data', 'products') ?? [];

        $sourceCode = [
            'Model' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
            'Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('ORM CRUD — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'products',
                'infoWhat' => $explanation['what'] ?? 'Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('products')
            ->withTitle('ORM CRUD')
            ->withSummary('Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.')
            ->withEntryLine('Define your schema once with attributes — reads, writes, and soft-deletes are handled by the ORM.')
            ->withHighlights(['#[FromTable]', '#[Column]', 'HasUuidV7', 'HasTimestamps', 'SoftDeletes', 'AbstractRepository'])
            ->withLearnMoreLabel('See the model & repository →')
            ->withDeepDiveLabel('How the ORM maps resources →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Repository Snapshot',
                'title' => 'Current product set',
                'summary' => 'A small working dataset backed by the ORM. Create and delete actions update this view.',
                'stats' => [
                    ['value' => (string) count($products), 'label' => 'Visible rows'],
                    ['value' => 'demo', 'label' => 'Tenant scope'],
                ],
                'columns' => ['Name', 'Price', 'Status'],
                'rows' => $rows,
                'emptyMessage' => 'No products yet — seed the data first.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
