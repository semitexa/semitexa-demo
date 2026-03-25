<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\RbacPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
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

    public function handle(RbacPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $headerCells = '<th>Permission</th>'
            . implode('', array_map(
                static fn(string $role) => '<th>' . ucfirst($role) . '</th>',
                array_keys(self::ROLE_MATRIX),
            ));

        $rows = '';
        foreach (self::ALL_PERMISSIONS as $perm) {
            $cells = '<td><code>' . htmlspecialchars($perm) . '</code></td>';
            foreach (self::ROLE_MATRIX as $rolePerms) {
                $has = in_array($perm, $rolePerms, true);
                $cells .= '<td>' . ($has ? '✓' : '—') . '</td>';
            }
            $rows .= '<tr>' . $cells . '</tr>';
        }

        $resultPreview = '<div class="result-preview">'
            . '<p>The demo has three roles. Each carries a set of permission slugs checked via <code>#[RequiresPermission]</code>.</p>'
            . '<table class="data-table">'
            . '<thead><tr>' . $headerCells . '</tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "#[RequiresPermission('products.write')]\n"
                . "#[AsPayload(path: '/admin/products/{id}', methods: ['PUT'])]\n"
                . "class UpdateProductPayload { ... }"
            )
            . '</pre>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('auth', 'rbac') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('RBAC — Semitexa Demo')
            ->withSection('auth')
            ->withSlug('rbac')
            ->withTitle('RBAC')
            ->withSummary('Role-based access control — assign permissions to roles, assign roles to users.')
            ->withEntryLine('Role-based access control — assign permissions to roles, assign roles to users.')
            ->withHighlights(['#[RequiresPermission]', '#[RequiresCapability]', 'RoleInterface', 'permission slugs'])
            ->withLearnMoreLabel('See the permission model →')
            ->withDeepDiveLabel('How RBAC resolution works →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
