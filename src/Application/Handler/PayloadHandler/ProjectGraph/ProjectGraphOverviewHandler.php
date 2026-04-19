<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\ProjectGraph;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\ProjectGraph\ProjectGraphOverviewPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProjectGraphOverviewPayload::class, resource: DemoFeatureResource::class)]
final class ProjectGraphOverviewHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(ProjectGraphOverviewPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'project-graph',
            'overview',
            'Project Graph Overview',
            'Understand what `semitexa-project-graph` adds: a stored structural map, an intelligence layer, and task-scoped context for large-codebase work.',
            ['semitexa-project-graph', 'task-first workflow', 'intelligence layer', 'stored structural graph'],
        );

        $explanation = [
            'what' => 'Project Graph is a package-level architecture memory for Semitexa repositories. It persists structural facts about modules, handlers, services, events, flows, and dependencies so engineers and AI can work from the actual system shape instead of rediscovering it repeatedly.',
            'how' => 'Use it on demand. Start from the task, fetch graph-backed context when structure matters, refresh the stored graph only when answers are stale, and then choose the narrowest command that answers the question: show, query, module, intelligence, impact, or context.',
            'why' => 'This matters because large repositories do not become easier just by adding more docs. Project Graph turns architecture into a reusable artifact: onboarding accelerates, impact analysis gets safer, AI prompts shrink, and structural questions stop triggering another round of blind grep.',
            'keywords' => [
                ['term' => 'task-first workflow', 'definition' => 'A graph-assisted workflow that starts from the task and only reaches for structural commands when the task actually needs them.'],
                ['term' => 'stored structural graph', 'definition' => 'A persisted architecture map of nodes and edges that can be reused across onboarding, review, and AI-assisted work.'],
                ['term' => 'intelligence layer', 'definition' => 'Higher-level answers built on top of the graph: hotspots, domain context, event lifecycles, inferred intent, and natural-language structural queries.'],
                ['term' => 'package-level capability', 'definition' => 'The graph surface belongs to the `semitexa-project-graph` package and is available where that package is installed and enabled.'],
            ],
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
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
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Project Graph is not a mandatory startup ritual. It is a package-level structural memory that you reach for when a task needs real architecture answers instead of guesses.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the package workflow →')
            ->withDeepDiveLabel('Why this matters for humans and AI →')
            ->withSourceCode([
                'Demo Package README' => $this->sourceCodeReader->readProjectRelativeSource('README.md'),
                'Package Workflow Notes' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Overview/Capabilities.example.md'),
                'Quickstart Commands' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Overview/Quickstart.example.sh'),
                'Command Surface Example' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Overview/CommandSurface.example.md'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Package Workflow',
                'title' => 'Start from the task, then reach for structural context only when the task needs it',
                'summary' => 'The important upgrade is not “one more command namespace.” It is that the repository can answer architecture questions from stored structure and intelligence instead of forcing every engineer or agent to rediscover the same system shape from scratch.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Start from the task',
                        'summary' => 'Project Graph is most useful when it is task-scoped. Begin with the work you are trying to do, then ask the graph for only the context that task actually needs.',
                        'commands' => [
                            'bin/semitexa ai:task "trace checkout architecture"',
                            'bin/semitexa ai:review-graph:context "trace checkout architecture" --format=json',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Refresh only when graph answers are stale',
                        'summary' => 'The graph is stored separately and can be refreshed incrementally. Rebuild it when you need fresh graph-backed answers, not as a reflex before every single edit.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:generate --json',
                            'bin/semitexa ai:review-graph:stats --json',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Choose the narrowest structural question',
                        'summary' => 'Once the graph exists, you do not need one generic “graph mode.” You can ask for a readable slice, a hotspot view, an impact radius, or a task-specific context package.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:show --format=markdown --module=Demo',
                            'bin/semitexa ai:review-graph:impact Semitexa\\\\Demo\\\\Application\\\\Service\\\\DemoCatalogService --json',
                            'bin/semitexa ai:review-graph:intelligence --hotspots',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Why Teams Care',
                    'rules' => [
                        'The graph surface belongs to the package, so teams can add this capability intentionally instead of pretending every install already has it.',
                        'Humans and AI both benefit from the same stored architecture artifact instead of maintaining separate onboarding rituals.',
                        'Impact, hotspots, flow traces, and task context are materially different answers; Project Graph gives each of them a dedicated surface.',
                        'Graph storage stays isolated from the main application database, so architecture data does not pollute runtime domain data.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Decision Model',
                'title' => 'The graph improves structural decision quality, not just command count',
                'summary' => 'Search still matters for local text facts. Project Graph matters when the question is architectural: what depends on this, what flows through here, what is high-risk, and what context actually belongs in the next review or AI prompt.',
                'columns' => ['Question', 'Without Project Graph', 'With Project Graph'],
                'rows' => [
                    [
                        ['text' => 'Do I need the graph for every task?'],
                        ['text' => 'Usually the team invents a ritual and runs it blindly.'],
                        ['text' => 'No. Start from the task and use graph commands only when structure matters.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'What changes once the package is enabled?'],
                        ['text' => 'Architecture stays scattered across files, docs, and oral explanations.'],
                        ['text' => 'The repository gains a stored graph plus intelligence and context-packing surfaces.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'What can it answer beyond node lists?'],
                        ['text' => 'Engineers must improvise with grep, memory, and assumption chains.'],
                        ['text' => 'Flows, events, hotspots, impact, module context, and AI-ready packages become explicit.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Why is this better than plain repository search?'],
                        ['text' => 'Search answers local text matches but not system shape.'],
                        ['text' => 'Graph-backed answers are structural, reusable, and safer for review or AI work.', 'variant' => 'success'],
                    ],
                ],
                'paragraphs' => [
                    'The practical gain is not just speed. It is that onboarding, refactors, code review, and AI work stop starting from zero every time.',
                    'That makes Project Graph a serious package capability, not decorative metadata and not a demo-only gimmick.',
                ],
                'note' => 'The promise is simple: less blind structural exploration, more reusable architectural truth.',
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Operational Notes',
                'title' => 'What makes the package workflow trustworthy',
                'summary' => 'The graph only helps when teams are explicit about scope, freshness, and installation context.',
                'rules' => [
                    'Project Graph uses its own named connection, so the architecture map stays separate from the main application database.',
                    'The default local fallback is a dedicated SQLite file under `var/tmp/project-graph.sqlite`, which makes first use straightforward.',
                    'Treat refresh as conditional: rebuild or verify freshness when graph-backed answers are stale, not as a startup ritual for every task.',
                    'Be explicit that `ai:review-graph:*` commands belong to installs where `semitexa-project-graph` is enabled.',
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
