<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Auth\RequiresPermissionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: RequiresPermissionPayload::class, resource: DemoFeatureResource::class)]
final class RequiresPermissionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(RequiresPermissionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'auth',
            slug: 'requires-permission',
            entryLine: 'Access control should be declarative: the payload names the required permission, and the framework enforces it automatically.',
            learnMoreLabel: 'See the guarded payload →',
            deepDiveLabel: 'How permission checks resolve →',
            relatedSlugs: [],
            fallbackTitle: 'Requires Permission',
            fallbackSummary: 'Declare one permission slug on the payload and let the framework enforce it before your handler runs.',
            fallbackHighlights: ['#[RequiresPermission]', '401 Unauthorized', '403 Forbidden', 'guard chain'],
            explanation: $this->explanationProvider->getExplanation('auth', 'requires-permission') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Guarded Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Auth/RequiresPermission/ManageUsersPayload.example.php'),
                'Guarded Handler' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Auth/RequiresPermission/ManageUsersHandler.example.php'),
            ])
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
            ]);
    }
}
