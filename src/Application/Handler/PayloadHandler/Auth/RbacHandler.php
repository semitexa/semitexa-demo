<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Authorization\Authorizer\Authorizer;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\RbacPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
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

        $explanation = $this->explanationProvider->getExplanation('auth', 'rbac') ?? [];

        $sourceCode = [
            'RBAC Demo Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Authorizer' => $this->sourceCodeReader->readClassSource(Authorizer::class),
            'SubjectGrantResolver' => $this->sourceCodeReader->readClassSource(SubjectGrantResolver::class),
            'CapabilityRegistry' => $this->sourceCodeReader->readClassSource(CapabilityRegistry::class),
            'PermissionProviderInterface' => $this->sourceCodeReader->readClassSource(PermissionProviderInterface::class),
        ];

        return $resource
            ->pageTitle('RBAC — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'auth',
                'currentSlug' => 'rbac',
                'infoWhat' => $explanation['what'] ?? 'Role-based access control — assign permissions to roles, assign roles to users.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('auth')
            ->withSlug('rbac')
            ->withTitle('RBAC')
            ->withSummary('Hybrid RBAC: bitmask-backed capabilities for broad checks, slug permissions for exact business rules, and module-owned catalogs behind one authorizer.')
            ->withEntryLine('Semitexa separates coarse-grained capabilities from fine-grained permission slugs so modules can extend authorization without coupling themselves to one storage model.')
            ->withHighlights(['#[RequiresCapability]', '#[RequiresPermission]', 'CapabilityRegistry', 'PermissionProviderInterface'])
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
