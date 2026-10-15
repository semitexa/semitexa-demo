<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Service\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Service\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Routing\PublicEndpointPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PublicEndpointPayload::class, resource: DemoFeatureResource::class)]
final class PublicEndpointHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(PublicEndpointPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'routing',
            slug: 'public-endpoint',
            entryLine: 'Anonymous access is never accidental: a payload is public only when it says #[AsPublicPayload].',
            learnMoreLabel: 'See the access contract →',
            deepDiveLabel: 'How the authorizer decides →',
            relatedSlugs: [],
            fallbackTitle: 'Public Endpoint',
            fallbackSummary: 'Every payload declares who may call it: #[AsPublicPayload], #[AsProtectedPayload] or #[AsServicePayload]. Public is the explicit opt-in.',
            fallbackHighlights: ['#[AsPublicPayload]', '#[AsProtectedPayload]', '401 Unauthorized', 'Authorizer'],
            explanation: $this->explanationProvider->getExplanation('routing', 'public-endpoint'),
            pageTitleSuffix: ' | Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Public Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PublicEndpoint/PublicCatalogPayload.example.php'),
                'Protected Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PublicEndpoint/ProtectedDashboardPayload.example.php'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/permission-matrix.html.twig', [
                'eyebrow' => 'Security Default',
                'title' => 'Private unless you say otherwise',
                'summary' => 'A payload that declares no access attribute is never routed, so public access is always deliberate and visible in code.',
                'columns' => ['Route declaration', 'Guest request result'],
                'rows' => [
                    [['text' => '#[AsPublicPayload]', 'code' => true], ['text' => '200 OK', 'variant' => 'success']],
                    [['text' => '#[AsProtectedPayload]', 'code' => true], ['text' => '401 Unauthorized', 'variant' => 'warning']],
                    [['text' => '#[AsServicePayload]', 'code' => true], ['text' => '401 Unauthorized', 'variant' => 'warning']],
                    [['text' => '#[AsPublicPayload] + #[RequiresPermission]', 'code' => true], ['text' => 'Boot-time exception', 'variant' => 'error']],
                    [['text' => 'No access attribute'], ['text' => 'Not routed', 'variant' => 'error']],
                ],
                'codeSnippet' => "// Signed-in users only\n#[AsProtectedPayload(path: '/dashboard', methods: ['GET'])]\nclass DashboardPayload {}\n\n// Explicitly public\n#[AsPublicPayload(path: '/catalog', methods: ['GET'])]\nclass CatalogPayload {}",
            ]);
    }
}
