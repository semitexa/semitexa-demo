<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Api\ApiShowcasePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ApiShowcasePayload::class, resource: DemoFeatureResource::class)]
final class ApiShowcaseHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    public function handle(ApiShowcasePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'api',
            slug: 'rest-api',
            entryLine: 'If you want clean REST, Semitexa already gives you a strong machine-facing contract without extra ceremony.',
            learnMoreLabel: 'See simple REST payloads →',
            deepDiveLabel: 'Why Semitexa REST stays clean →',
            relatedSlugs: [],
            fallbackTitle: 'REST API',
            fallbackSummary: 'Classic Semitexa REST endpoints with typed payloads, versioning, and consumer-friendly response shaping.',
            fallbackHighlights: ['#[ExternalApi]', '#[ApiVersion]', 'application/ld+json', 'fields', 'expand', 'X-Response-Profile'],
            explanation: $this->explanationProvider->getExplanation('api', 'rest-api') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'List Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Rest/ProductListPayload.example.php'),
                'Detail Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Rest/ProductDetailPayload.example.php'),
                'REST Request' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Rest/ProductListRequest.example.txt'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/api-showcase.html.twig', [
                'profiles' => $this->apiPresenter->getShowcaseProfiles(),
                'collectionHref' => '/demo/api/v1/products?q=headphones',
                'detailHref' => '/demo/api/v1/products/wireless-headphones?profile=full&expand=category,reviews',
                'jsonLdHref' => '/demo/api/v1/products/wireless-headphones?format=ld',
            ]);
    }
}
