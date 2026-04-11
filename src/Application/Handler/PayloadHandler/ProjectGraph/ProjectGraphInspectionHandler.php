<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\ProjectGraph;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\ProjectGraph\ProjectGraphInspectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProjectGraphInspectionPayload::class, resource: DemoFeatureResource::class)]
final class ProjectGraphInspectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ProjectGraphInspectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'After generation, the graph becomes an exploration surface. You can render graph views, search nodes, inspect dependencies, trace usages, and expose cross-module edges without reconstructing the whole architecture manually.',
            'how' => 'Use `ai:review-graph:show` for summaries or exports, `ai:review-graph:query` for ad hoc structural questions, and `ai:review-graph:capabilities` when the goal is a command- and AI-oriented overview instead of raw edges.',
            'why' => 'This is where the usability payoff becomes obvious. Engineers can answer architectural questions in seconds, reviewers can verify module coupling directly, and AI tooling can consume project capabilities as structured output instead of loose prose.',
            'keywords' => [
                ['term' => 'ai:review-graph:show', 'definition' => 'Displays or exports graph views in summary, JSON, DOT, or Markdown formats.'],
                ['term' => 'ai:review-graph:query', 'definition' => 'Runs ad hoc graph queries such as search, usages, dependencies, and cross-module edge inspection.'],
                ['term' => 'cross-module edges', 'definition' => 'Dependencies that cross module boundaries and often matter most during review and architecture cleanup.'],
                ['term' => 'capability manifest', 'definition' => 'A graph-derived summary of project commands and capabilities that is easier for AI and operators to consume than raw graph storage.'],
            ],
        ];

        return $resource
            ->pageTitle('Inspecting the Project Graph — Semitexa Demo')
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
            ->withTitle('Inspecting the Graph')
            ->withSummary('Explore modules, dependencies, usages, and capabilities directly from the graph instead of piecing them together from file-by-file searches.')
            ->withEntryLine('Once the graph is built, architectural questions become terminal queries instead of archaeology.')
            ->withHighlights(['ai:review-graph:show', 'ai:review-graph:query', 'cross-module edges', 'capability manifest'])
            ->withLearnMoreLabel('See the inspection workflows →')
            ->withDeepDiveLabel('What this saves during real project work →')
            ->withSourceCode([
                'Inspection Commands' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/ProjectGraph/Inspection/Queries.example.sh'),
                'Show Formats' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/ProjectGraph/Inspection/ShowFormats.example.sh'),
                'Capabilities Markdown' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/ProjectGraph/Inspection/Capabilities.example.md'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Inspection Flow',
                'title' => 'Move from “what is in this repo?” to “show me the exact structural answer”',
                'summary' => 'This workflow is about convenience with substance. The graph does not replace engineering judgment; it removes needless discovery friction so judgment can start earlier.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Render the current graph view',
                        'summary' => 'Start broad: summary for a quick scan, Markdown for reviewable output, or DOT when you want export-oriented visualization.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:show',
                            'bin/semitexa ai:review-graph:show --format=markdown --module=Demo',
                            'bin/semitexa ai:review-graph:show --format=dot Demo',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Ask dependency questions directly',
                        'summary' => 'Search nodes, trace usages, inspect dependencies, or isolate cross-module edges when you want to understand coupling.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:query --search=DemoCatalogService',
                            'bin/semitexa ai:review-graph:query --dependencies=Semitexa\\\\Demo\\\\Application\\\\Service\\\\DemoCatalogService',
                            'bin/semitexa ai:review-graph:query --cross-module --from=Demo --to=Core',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Project the graph into capability language',
                        'summary' => 'When the goal is operator or AI understanding, capability output is often more useful than raw nodes and edges.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:capabilities --markdown',
                            'bin/semitexa ai:review-graph:capabilities --module=Demo --category=introspection',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Practical Benefit',
                    'rules' => [
                        'Reviewers can spot module leaks earlier by asking for cross-module edges directly.',
                        'Onboarding gets easier because people can ask architectural questions in the terminal without already knowing the answer.',
                        'AI prompts improve when capability output replaces vague guesses about what the project supports.',
                        'Teams save time because common dependency questions stop triggering another full round of grep and tab-hopping.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Query Surface',
                'title' => 'The graph supports several kinds of exploration, each useful at a different stage',
                'summary' => 'Not every question needs the same output. Project Graph provides summary, raw query, and capability-oriented views so engineers can choose the right level of detail.',
                'columns' => ['Need', 'Best command', 'Why it is useful'],
                'rows' => [
                    [
                        ['text' => 'Quick health and size check'],
                        ['text' => 'ai:review-graph:stats'],
                        ['text' => 'Confirms the graph is current enough to trust.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Module or focus-node view'],
                        ['text' => 'ai:review-graph:show'],
                        ['text' => 'Gives a readable structural slice without writing custom queries.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Dependency or usage tracing'],
                        ['text' => 'ai:review-graph:query'],
                        ['text' => 'Answers targeted architectural questions fast.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'AI/operator command surface'],
                        ['text' => 'ai:review-graph:capabilities'],
                        ['text' => 'Turns structure into a practical manifest instead of raw edges.', 'variant' => 'success'],
                    ],
                ],
                'paragraphs' => [
                    'This flexibility is part of the convenience story: the same stored graph supports several different high-value workflows.',
                    'That means one investment in graph generation pays off across review, onboarding, operations, and AI assistance.',
                ],
                'note' => 'The right question should determine the output format, not the other way around.',
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'When This Shines',
                'title' => 'High-friction situations that become much easier',
                'summary' => 'The graph is especially valuable when normal repository familiarity breaks down.',
                'rules' => [
                    'Use graph queries when a class or module name is familiar but its actual coupling is not.',
                    'Use cross-module inspection before “small” cleanup tasks that might secretly cross boundaries.',
                    'Use Markdown or JSON output when you need to share or automate the result instead of reading it once in a terminal.',
                    'Use capability manifests for AI and operator workflows where concise, structured project understanding matters more than raw implementation detail.',
                ],
            ])
            ->withRelatedPayloads([
                ['href' => '/demo/project-graph/overview', 'label' => 'Project Graph Overview'],
                ['href' => '/demo/project-graph/impact', 'label' => 'Impact, Context, and Watch Mode'],
            ])
            ->withExplanation($explanation);
    }
}
