<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Authorization\Authorizer\Authorizer;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\RbacPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Rbac\Capability\CapabilityRegistry;
use Semitexa\Rbac\Contract\PermissionProviderInterface;
use Semitexa\Rbac\Resolver\SubjectGrantResolver;

#[AsPayloadHandler(payload: RbacPayload::class, resource: DemoFeatureResource::class)]
final class RbacHandler implements TypedHandlerInterface
{
    private const ROLE_MATRIX = [
        'admin'  => ['products.read', 'products.write', 'users.manage', 'orders.manage', 'settings.manage'],
        'editor' => ['products.read', 'products.write'],
        'viewer' => ['products.read'],
    ];

    private const ALL_PERMISSIONS = [
        'products.read', 'products.write',
        'users.manage', 'orders.manage', 'settings.manage',
    ];

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(RbacPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $columns = array_merge(['Permission'], array_map('ucfirst', array_keys(self::ROLE_MATRIX)));
        $rows = [];
        foreach (self::ALL_PERMISSIONS as $perm) {
            $cells = [['text' => $perm, 'code' => true]];
            foreach (self::ROLE_MATRIX as $rolePerms) {
                $has = in_array($perm, $rolePerms, true);
                $cells[] = ['text' => $has ? 'Granted' : 'Denied', 'variant' => $has ? 'success' : 'neutral'];
            }
            $rows[] = $cells;
        }

        $presentation = $this->documents->resolve(
            'auth',
            'rbac',
            'RBAC',
            'Hybrid RBAC with coarse-grained capabilities, exact permission slugs, and module-owned permission catalogs.',
            ['#[RequiresCapability]', '#[RequiresPermission]', 'CapabilityRegistry', 'PermissionProviderInterface'],
        );
        $explanation = $this->explanationProvider->getExplanation('auth', 'rbac') ?? [];

        $sourceCode = [
            'RBAC Demo Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Authorizer' => $this->sourceCodeReader->readClassSource(Authorizer::class),
            'SubjectGrantResolver' => $this->sourceCodeReader->readClassSource(SubjectGrantResolver::class),
            'CapabilityRegistry' => $this->sourceCodeReader->readClassSource(CapabilityRegistry::class),
            'PermissionProviderInterface' => $this->sourceCodeReader->readClassSource(PermissionProviderInterface::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'auth',
                'currentSlug' => 'rbac',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('auth')
            ->withSlug('rbac')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Semitexa separates coarse-grained capabilities from fine-grained permission slugs so modules can extend authorization without coupling themselves to one storage model.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the hybrid permission model →')
            ->withDeepDiveLabel('How grant resolution and module extension work →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/permission-matrix.html.twig', [
                'eyebrow' => 'RBAC',
                'title' => 'Hybrid grant model',
                'summary' => 'Capabilities cover broad platform rights, permission slugs cover exact business actions, and any module can add its own permission list by implementing the RBAC provider contract.',
                'columns' => $columns,
                'rows' => $rows,
                'codeSnippet' => "#[RequiresCapability(AdminCapability::BackofficeAccess)]\n#[RequiresPermission('products.write')]\n#[AsPayload(path: '/admin/products/{id}', methods: ['PUT'])]\nclass UpdateProductPayload { ... }\n\n// A domain module supplies slug permissions through PermissionProviderInterface.",
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
