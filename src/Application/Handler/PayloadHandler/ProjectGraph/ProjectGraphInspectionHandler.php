<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\ProjectGraph;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\ProjectGraph\ProjectGraphInspectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProjectGraphInspectionPayload::class, resource: DemoFeatureResource::class)]
final class ProjectGraphInspectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(ProjectGraphInspectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'project-graph',
            'inspection',
            'Inspecting the Graph',
            'Use Project Graph queries and intelligence views to inspect modules, dependencies, flows, events, and hotspots without reconstructing the repository manually.',
            ['ai:review-graph:show', 'ai:review-graph:query', 'ai:review-graph:module', 'ai:review-graph:intelligence'],
        );

        $explanation = [
            'what' => 'Once the package is enabled and the graph exists, inspection becomes a set of explicit structural views instead of improvised archaeology. You can render slices, query dependencies, inspect whole modules, and ask the intelligence layer for hotspots, doc gaps, or event lifecycles.',
            'how' => 'Use `ai:review-graph:show` for readable slices, `ai:review-graph:query` for targeted dependency questions, `ai:review-graph:module` for a module-level overview, and `ai:review-graph:intelligence` when the right answer is not just raw edges but a higher-level structural explanation.',
            'why' => 'This is where the package becomes operationally useful. Reviews get faster, onboarding becomes less fragile, and AI tools can start from architecture-backed answers instead of broad guesses assembled from random files.',
            'keywords' => [
                ['term' => 'ai:review-graph:show', 'definition' => 'Renders summary, markdown, JSON, or DOT graph views for a chosen focus, module, or node type.'],
                ['term' => 'ai:review-graph:query', 'definition' => 'Runs structural lookups such as search, usages, dependencies, and cross-module edge inspection.'],
                ['term' => 'ai:review-graph:module', 'definition' => 'Builds a module overview with summary counts, domain context, hotspots, and optional flows or event details.'],
                ['term' => 'ai:review-graph:intelligence', 'definition' => 'Queries the higher-level intelligence layer for hotspots, documentation gaps, flows, event lifecycles, intent, and natural-language structural answers.'],
            ],
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'project-graph',
                'currentSlug' => 'inspection',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('project-graph')
            ->withSectionLabel('Project Graph')
            ->withSlug('inspection')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Once Project Graph is enabled, structural questions stop being archaeology. You can ask for exactly the slice, dependency, hotspot, or module view you need.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the inspection workflows →')
            ->withDeepDiveLabel('What this saves during real project work →')
            ->withSourceCode([
                'Inspection Commands' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Inspection/Queries.example.sh'),
                'Show Formats' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Inspection/ShowFormats.example.sh'),
                'Intelligence Notes' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Inspection/Capabilities.example.md'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Inspection Flow',
                'title' => 'Move from “what is in this repo?” to a focused structural answer',
                'summary' => 'Inspection is not one command. It is a small family of surfaces that let you choose the right answer shape for the question: rendered slice, query result, module overview, or intelligence-backed explanation.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Render the slice you need',
                        'summary' => 'Start broad when you need a structural snapshot, then narrow by module, node type, or focus target when the question is localized.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:show --format=markdown --module=Demo',
                            'bin/semitexa ai:review-graph:show --format=json --type=service,handler',
                            'bin/semitexa ai:review-graph:show --format=dot Demo',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Ask focused structural questions',
                        'summary' => 'When the question is about coupling, usage, or module boundaries, query directly instead of reconstructing the answer from imports and memory.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:query --search=DemoCatalogService',
                            'bin/semitexa ai:review-graph:query --dependencies=Semitexa\\\\Demo\\\\Application\\\\Service\\\\DemoCatalogService',
                            'bin/semitexa ai:review-graph:query --cross-module --from=Demo --to=Core',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Reach for intelligence when edges are not enough',
                        'summary' => 'Some questions need richer answers than a node list: hotspots, event lifecycles, module summaries, inferred intent, and task context.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:module Demo --include-events --include-flows --format=json',
                            'bin/semitexa ai:review-graph:intelligence --hotspots',
                            'bin/semitexa ai:review-graph:context "review Demo module coupling" --format=json',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Practical Benefit',
                    'rules' => [
                        'Reviewers can spot module leaks and hotspots from explicit graph-backed answers instead of architectural intuition alone.',
                        'Onboarding gets easier because new engineers can ask structural questions without already knowing where to look.',
                        'AI workflows improve because module and context views are narrower and more defensible than arbitrary file dumps.',
                        'Teams save time because common dependency and module questions stop triggering another round of grep and tab-hopping.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Query Surface',
                'title' => 'Different inspection questions deserve different answer shapes',
                'summary' => 'The graph is more useful when teams stop treating it as one generic command. Broad slices, direct queries, module views, intelligence helpers, and task context each serve a distinct inspection job.',
                'columns' => ['Need', 'Best command', 'Why it is useful'],
                'rows' => [
                    [
                        ['text' => 'Broad structural slice'],
                        ['text' => 'ai:review-graph:show'],
                        ['text' => 'Renders a readable view without inventing custom queries first.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Dependency, usage, or search question'],
                        ['text' => 'ai:review-graph:query'],
                        ['text' => 'Answers targeted structural questions fast.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Whole-module understanding'],
                        ['text' => 'ai:review-graph:module'],
                        ['text' => 'Packages counts, context, hotspots, and optional flows or event details in one response.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Higher-level structural explanation'],
                        ['text' => 'ai:review-graph:intelligence'],
                        ['text' => 'Surfaces hotspots, doc gaps, event lifecycles, and natural-language answers.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Task-scoped prep for review or AI work'],
                        ['text' => 'ai:review-graph:context'],
                        ['text' => 'Builds a tighter structural package than random file sampling.', 'variant' => 'success'],
                    ],
                ],
                'paragraphs' => [
                    'This is part of the real value proposition: one stored graph can power several very different inspection workflows without forcing teams back into archaeology.',
                    'That means one structural artifact can pay off across onboarding, debugging, review, refactors, and AI assistance.',
                ],
                'note' => 'The right structural question should determine the command surface, not the other way around.',
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'When This Shines',
                'title' => 'High-friction situations that become much easier',
                'summary' => 'The graph is especially valuable when normal repository familiarity breaks down.',
                'rules' => [
                    'Start with `show` or `module` when you need a readable slice, then drop to `query` only when the question is specific.',
                    'Use cross-module and dependency inspection before “small” cleanup tasks that might secretly cross boundaries.',
                    'Use JSON or Markdown output when the result needs to be shared, reviewed, or consumed by automation.',
                    'Use `intelligence` or `context` when the goal is explanation or task preparation rather than raw graph edges.',
                ],
            ])
            ->withRelatedPayloads([
                ['href' => '/demo/project-graph/overview', 'label' => 'Project Graph Overview'],
                ['href' => '/demo/project-graph/impact', 'label' => 'Impact, Context, and Watch Mode'],
            ])
            ->withExplanation($explanation);
    }
}
