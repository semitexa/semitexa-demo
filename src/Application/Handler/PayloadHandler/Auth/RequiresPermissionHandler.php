<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\RequiresPermissionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: RequiresPermissionPayload::class, resource: DemoFeatureResource::class)]
final class RequiresPermissionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(RequiresPermissionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('auth', 'requires-permission') ?? [];

        $sourceCode = [
            'Guarded Payload' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Auth/RequiresPermission/ManageUsersPayload.example.php'),
            'Guarded Handler' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Auth/RequiresPermission/ManageUsersHandler.example.php'),
        ];

        return $resource
            ->pageTitle('Requires Permission — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'auth',
                'currentSlug' => 'requires-permission',
                'infoWhat' => $explanation['what'] ?? 'A permission slug on the payload is enough for the framework to protect the endpoint.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('auth')
            ->withSlug('requires-permission')
            ->withTitle('Requires Permission')
            ->withSummary('Declare one permission slug on the payload and let the framework enforce it before your handler runs.')
            ->withEntryLine('Access control should be declarative: the payload names the required permission, and the framework enforces it automatically.')
            ->withHighlights(['#[RequiresPermission]', '401 Unauthorized', '403 Forbidden', 'guard chain'])
            ->withLearnMoreLabel('See the guarded payload →')
            ->withDeepDiveLabel('How permission checks resolve →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/permission-matrix.html.twig', [
                'eyebrow' => 'Permission Guard',
                'title' => 'One attribute, three outcomes',
                'summary' => 'The same payload declaration produces different outcomes depending on whether the subject is a guest, authenticated without the permission, or authenticated with the permission.',
                'columns' => ['Request state', 'Framework decision'],
                'rows' => [
                    [['text' => 'Guest subject'], ['text' => '401 Unauthorized', 'variant' => 'warning']],
                    [['text' => 'Authenticated, missing permission'], ['text' => '403 Forbidden', 'variant' => 'error']],
                    [['text' => 'Authenticated, permission granted'], ['text' => '200 OK', 'variant' => 'success']],
                ],
                'codeSnippet' => "#[RequiresPermission('users.manage')]\n#[AsPayload(path: '/admin/users', methods: ['GET'])]\nclass ManageUsersPayload {}\n\nfinal class ManageUsersHandler implements TypedHandlerInterface\n{\n    public function handle(ManageUsersPayload \$payload, AdminPageResource \$resource): AdminPageResource\n    {\n        // No manual access checks here.\n        return \$resource;\n    }\n}",
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
