<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\ProjectGraph;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\ProjectGraph\ProjectGraphOverviewPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProjectGraphOverviewPayload::class, resource: DemoFeatureResource::class)]
final class ProjectGraphOverviewHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ProjectGraphOverviewPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'The Project Graph package builds a live structural map of the current Semitexa codebase. That graph is persisted separately, can be refreshed incrementally, and gives both humans and AI a fast architectural starting point before deep changes.',
            'how' => 'Run `ai:review-graph:generate` to build or refresh the graph, `ai:review-graph:stats` to confirm health, and `ai:review-graph:capabilities` to turn the structural data into a practical manifest of commands and project capabilities.',
            'why' => 'This is a high-leverage feature because it removes one of the most expensive forms of waste in real projects: rediscovering the same architecture over and over. Teams get faster onboarding, safer edits, cleaner AI context, and a more reviewable path into large codebases.',
            'keywords' => [
                ['term' => 'ai:review-graph:generate', 'definition' => 'Builds or incrementally refreshes the stored project graph from the current repository.'],
                ['term' => 'ai:review-graph:stats', 'definition' => 'Shows graph health, indexed file count, node counts, edge counts, and last generation time.'],
                ['term' => 'ai:review-graph:capabilities', 'definition' => 'Projects graph data into an AI-friendly capability manifest grouped by command kinds and project context.'],
                ['term' => 'incremental graph', 'definition' => 'A graph that refreshes only changed parts of the codebase instead of rebuilding everything on every run.'],
            ],
        ];

        return $resource
            ->pageTitle('Project Graph Overview — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'project-graph',
                'currentSlug' => 'overview',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('project-graph')
            ->withSectionLabel('Project Graph')
            ->withSlug('overview')
            ->withTitle('Project Graph Overview')
            ->withSummary('Build a live structural map of the Semitexa codebase so engineers and AI agents can start from the real architecture, not from blind searching.')
            ->withEntryLine('This is the fastest way to make a large Semitexa repository feel legible: generate the graph once, verify it, and inspect capabilities before deep edits or AI-assisted work.')
            ->withHighlights(['ai:review-graph:generate', 'ai:review-graph:stats', 'ai:review-graph:capabilities', 'incremental graph'])
            ->withLearnMoreLabel('See the quick start →')
            ->withDeepDiveLabel('Why this becomes a real engineering advantage →')
            ->withSourceCode([
                'Package README' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-project-graph/README.md'),
                'Quickstart Commands' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Overview/Quickstart.example.sh'),
                'Capabilities Example' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Overview/Capabilities.example.md'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Quick Start',
                'title' => 'Turn the repository into a queryable system map in three commands',
                'summary' => 'The first win is speed and clarity. Instead of opening dozens of files just to understand the shape of the app, you build the graph once and immediately get health, capabilities, and a stable AI-ready view of the project.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Build or refresh the graph',
                        'summary' => 'Start with an incremental refresh for normal work. Use `--full` when you need a complete rebuild.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:generate --json',
                            'bin/semitexa ai:review-graph:generate --full',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Verify that the graph is healthy',
                        'summary' => 'Confirm that files were indexed, nodes and edges exist, and the graph is recent enough to trust.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:stats --json',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Ask what the project can do',
                        'summary' => 'Project Graph can derive an AI-friendly command and capability surface instead of leaving that knowledge implicit.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:capabilities --markdown',
                            'bin/semitexa ai:review-graph:capabilities --category=graph',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Why Teams Care',
                    'rules' => [
                        'New engineers can understand the system faster without depending on oral walkthroughs alone.',
                        'Experienced engineers stop paying the repeated cost of rediscovering module boundaries and dependency paths.',
                        'AI agents get a safer, structure-backed starting point before editing code.',
                        'The graph storage is isolated from the main application database, so the architecture map does not pollute normal runtime data.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Value Proposition',
                'title' => 'Why this feels materially better than plain repository search',
                'summary' => 'Search is still useful, but it answers local text questions. Project Graph answers structural questions that matter during onboarding, debugging, refactors, and AI-assisted code changes.',
                'columns' => ['Question', 'Without Project Graph', 'With Project Graph'],
                'rows' => [
                    [
                        ['text' => 'What should I run first?'],
                        ['text' => 'Open docs, search commands, and infer the likely workflow.'],
                        ['text' => 'Generate, verify stats, inspect capabilities.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'What does this module depend on?'],
                        ['text' => 'Chase imports and framework attributes manually.'],
                        ['text' => 'Query graph edges and focused views directly.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Can AI safely start here?'],
                        ['text' => 'Only after ad hoc file sampling and assumptions.'],
                        ['text' => 'Yes, from a stored structural map and capability manifest.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'How expensive is repo rediscovery?'],
                        ['text' => 'Paid again by every person and every task.'],
                        ['text' => 'Amortized into one reusable graph artifact.', 'variant' => 'success'],
                    ],
                ],
                'paragraphs' => [
                    'This is why Project Graph is worth surfacing as a marketing feature, not hiding as an internal implementation detail.',
                    'It improves comprehension, not just tooling aesthetics, and that improvement compounds across every future task on the repository.',
                ],
                'note' => 'The practical promise is simple: less blind exploration, faster trust in the codebase, and safer high-context work for both humans and AI.',
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Operational Notes',
                'title' => 'What makes the workflow trustworthy',
                'summary' => 'A graph is only useful if teams can trust where it lives, how it is refreshed, and when it is stale.',
                'rules' => [
                    'Project Graph uses its own named connection, so the architecture map stays separate from the main application database.',
                    'The default local fallback is a dedicated SQLite file under `var/tmp/project-graph.sqlite`, which makes first use straightforward.',
                    'Run `ai:review-graph:generate` before deep edits, code generation, or large AI tasks so the structural view is current.',
                    'Treat `ai:review-graph:stats` as the “is this graph trustworthy?” command before you use graph-driven answers in serious work.',
                ],
            ])
            ->withRelatedPayloads([
                ['href' => '/demo/project-graph/inspection', 'label' => 'Inspecting the Graph'],
                ['href' => '/demo/project-graph/impact', 'label' => 'Impact, Context, and Watch Mode'],
                ['href' => '/demo/cli/ai-tooling', 'label' => 'AI Tooling Surface'],
            ])
            ->withExplanation($explanation);
    }
}
