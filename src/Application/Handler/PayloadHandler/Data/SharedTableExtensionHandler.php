<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Data\SharedTableExtensionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Orm\Schema\SchemaCollector;

#[AsPayloadHandler(payload: SharedTableExtensionPayload::class, resource: DemoFeatureResource::class)]
final class SharedTableExtensionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(SharedTableExtensionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'data',
            slug: 'table-extension',
            entryLine: 'This is the ORM painkiller: later modules add columns to an existing table without reopening the original resource class.',
            learnMoreLabel: 'See both modules side by side →',
            deepDiveLabel: 'Why this is a real ORM advantage →',
            relatedSlugs: [],
            fallbackTitle: 'Shared Table Extension',
            fallbackSummary: 'Two modules can extend one table independently, and the ORM merges the schema without forcing either side to edit the other.',
            fallbackHighlights: ['#[FromTable]', 'SchemaCollector', 'Module isolation', '#[Column]', '#[TenantScoped]'],
            explanation: $this->explanationProvider->getExplanation('data', 'table-extension') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Catalog Module Resource' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/SharedTable/CatalogProductResource.example.php'),
                'Merchandising Module Extension' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/SharedTable/MerchandisingProductExtension.example.php'),
                'Schema Merge Logic' => $this->sourceCodeReader->readClassSource(SchemaCollector::class),
                'Feature Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/shared-table-extension.html.twig', [
                'painPoints' => [
                    'One module usually becomes the permanent owner of a table, even when another bounded context needs to extend it later.',
                    'The follow-up module often has to edit the original model class, creating ownership bleed and merge pressure between teams.',
                    'That coupling turns harmless extra columns into a cross-module coordination problem instead of an additive change.',
                ],
                'moduleColumns' => [
                    [
                        'title' => 'Catalog Module',
                        'badge' => 'Base resource',
                        'tone' => 'base',
                        'summary' => 'Owns the initial product shape that the storefront and admin tools already depend on.',
                        'columns' => ['id', 'tenant_id', 'name', 'description', 'price', 'status'],
                    ],
                    [
                        'title' => 'Merchandising Module',
                        'badge' => 'Extension resource',
                        'tone' => 'extension',
                        'summary' => 'Arrives later and adds campaign-only fields without reopening the catalog module.',
                        'columns' => ['badge_label', 'merch_priority', 'campaign_code'],
                    ],
                ],
                'mergedColumns' => [
                    ['name' => 'id', 'owner' => 'Catalog', 'note' => 'Base primary key stays untouched.'],
                    ['name' => 'tenant_id', 'owner' => 'Catalog', 'note' => 'Tenant scoping still applies to the shared table.'],
                    ['name' => 'name', 'owner' => 'Catalog', 'note' => 'Core commerce data remains where it started.'],
                    ['name' => 'description', 'owner' => 'Catalog', 'note' => 'Existing product content is unchanged.'],
                    ['name' => 'price', 'owner' => 'Catalog', 'note' => 'Base module continues to own pricing semantics.'],
                    ['name' => 'status', 'owner' => 'Catalog', 'note' => 'Lifecycle state stays with the core domain.'],
                    ['name' => 'badge_label', 'owner' => 'Merchandising', 'note' => 'Added later for campaign UX.'],
                    ['name' => 'merch_priority', 'owner' => 'Merchandising', 'note' => 'Supports merchandising sort logic.'],
                    ['name' => 'campaign_code', 'owner' => 'Merchandising', 'note' => 'Connects rows to external campaign flows.'],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/shared-table-rules.html.twig', [
                'rules' => [
                    'Both modules point to the same physical table name via #[FromTable].',
                    'SchemaCollector groups discovered resources by table and only adds missing columns.',
                    'The extension module contributes new columns without redefining the catalog columns.',
                    'No module needs to reopen the other module\'s class just to add campaign-specific state.',
                ],
            ]);
    }
}
