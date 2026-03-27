<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Exception\NotFoundException;
use Semitexa\Demo\Application\Payload\Request\DemoFeaturePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureRegistry;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

final class DemoFeatureHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeatureRegistry $featureRegistry;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DemoFeaturePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $section = $payload->getSection();
        $slug = $payload->getFeature();

        $feature = $this->featureRegistry->getBySlug($section, $slug);

        if ($feature === null) {
            throw new NotFoundException('Demo feature', $section . '/' . $slug);
        }

        $explanation = $this->explanationProvider->getExplanation($section, $slug) ?? [
            'what' => $feature->summary,
            'how' => '',
            'why' => '',
            'keywords' => [],
        ];

        // Read source code for the payload class that carries this feature
        $features = $this->featureRegistry->getBySection($section);
        $sourceCode = [];
        foreach ($features as $entry) {
            if ($entry['attribute']->slug === $slug) {
                $className = $entry['class'];
                $source = $this->sourceCodeReader->readClassSource($className);
                if ($source !== '') {
                    $sourceCode['Payload'] = $source;
                }
                break;
            }
        }

        return $resource
            ->pageTitle($feature->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => $section,
                'currentSlug' => $slug,
                'infoWhat' => $explanation['what'] ?? $feature->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection($section)
            ->withSlug($slug)
            ->withTitle($feature->title)
            ->withSummary($feature->summary)
            ->withEntryLine($feature->entryLine ?: $feature->summary)
            ->withHighlights($feature->highlights)
            ->withLearnMoreLabel($feature->learnMoreLabel)
            ->withDeepDiveLabel($feature->deepDiveLabel)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withRelatedPayloads($feature->relatedPayloads);
    }
}
