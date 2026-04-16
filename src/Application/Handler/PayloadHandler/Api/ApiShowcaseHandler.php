<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Api\ApiShowcasePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ApiShowcasePayload::class, resource: DemoFeatureResource::class)]
final class ApiShowcaseHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoApiPresenter $apiPresenter;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(ApiShowcasePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'api',
            'rest-api',
            'REST API',
            'Classic Semitexa REST endpoints with typed payloads, versioning, and consumer-friendly response shaping.',
            ['#[ExternalApi]', '#[ApiVersion]', 'application/ld+json', 'fields', 'expand', 'X-Response-Profile'],
        );
        $explanation = $this->explanationProvider->getExplanation('api', 'rest-api') ?? [];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'api',
                'currentSlug' => 'rest-api',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('api')
            ->withSlug('rest-api')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('If you want clean REST, Semitexa already gives you a strong machine-facing contract without extra ceremony.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See simple REST payloads →')
            ->withDeepDiveLabel('Why Semitexa REST stays clean →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/api-showcase.html.twig', [
                'profiles' => $this->apiPresenter->getShowcaseProfiles(),
                'collectionHref' => '/demo/api/v1/products?q=headphones',
                'detailHref' => '/demo/api/v1/products/wireless-headphones?profile=full&expand=category,reviews',
                'jsonLdHref' => '/demo/api/v1/products/wireless-headphones?format=ld',
            ])
            ->withSourceCode([
                'List Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Rest/ProductListPayload.example.php'),
                'Detail Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Rest/ProductDetailPayload.example.php'),
                'REST Request' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Api/Rest/ProductListRequest.example.txt'),
            ])
            ->withExplanation($explanation);
    }
}
