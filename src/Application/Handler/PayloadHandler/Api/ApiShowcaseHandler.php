<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Api;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Api\ApiShowcasePayload;
use Semitexa\Demo\Application\Payload\Request\Api\ProductDetailPayload;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
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

    public function handle(ApiShowcasePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('api', 'external-endpoints') ?? [];

        return $resource
            ->pageTitle('Consumer Profiles Showcase — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'api',
                'currentSlug' => 'showcase',
                'infoWhat' => $explanation['what'] ?? 'One endpoint can serve multiple API consumers without branching into separate handler trees.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('api')
            ->withSlug('showcase')
            ->withTitle('Consumer Profiles Showcase')
            ->withSummary('One product API, multiple consumers: frontend JSON, JSON-LD crawlers, expanded admin views, and search-oriented collections.')
            ->withEntryLine('The same Semitexa product endpoint shifts shape depending on who asks and how they ask.')
            ->withHighlights(['#[ExternalApi]', '#[ApiVersion]', 'application/ld+json', 'fields', 'expand', 'X-Response-Profile'])
            ->withLearnMoreLabel('See live endpoint contracts →')
            ->withDeepDiveLabel('API presenter internals →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/api-showcase.html.twig', [
                'profiles' => $this->apiPresenter->getShowcaseProfiles(),
                'collectionHref' => '/demo/api/v1/products?q=headphones',
                'detailHref' => '/demo/api/v1/products/wireless-headphones?profile=full&expand=category,reviews',
                'jsonLdHref' => '/demo/api/v1/products/wireless-headphones?format=ld',
            ])
            ->withSourceCode([
                'Showcase Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Product List Payload' => $this->sourceCodeReader->readClassSource(ProductListPayload::class),
                'Product Detail Payload' => $this->sourceCodeReader->readClassSource(ProductDetailPayload::class),
                'DemoApiPresenter' => $this->sourceCodeReader->readClassSource(DemoApiPresenter::class),
            ])
            ->withExplanation($explanation);
    }
}
