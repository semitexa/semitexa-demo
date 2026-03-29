<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\RbacPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

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
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
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
            ->withSummary('Role-based access control — assign permissions to roles, assign roles to users.')
            ->withEntryLine('Role-based access control — assign permissions to roles, assign roles to users.')
            ->withHighlights(['#[RequiresPermission]', '#[RequiresCapability]', 'RoleInterface', 'permission slugs'])
            ->withLearnMoreLabel('See the permission model →')
            ->withDeepDiveLabel('How RBAC resolution works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/permission-matrix.html.twig', [
                'eyebrow' => 'RBAC',
                'title' => 'Role-to-permission matrix',
                'summary' => 'Each route attribute maps to a permission slug, and roles simply collect the slugs they are allowed to perform.',
                'columns' => $columns,
                'rows' => $rows,
                'codeSnippet' => "#[RequiresPermission('products.write')]\n#[AsPayload(path: '/admin/products/{id}', methods: ['PUT'])]\nclass UpdateProductPayload { ... }",
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
