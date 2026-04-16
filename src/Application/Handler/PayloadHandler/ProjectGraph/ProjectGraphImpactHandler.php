<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\ProjectGraph;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\ProjectGraph\ProjectGraphImpactPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProjectGraphImpactPayload::class, resource: DemoFeatureResource::class)]
final class ProjectGraphImpactHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(ProjectGraphImpactPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'project-graph',
            'impact',
            'Impact, Context, and Watch Mode',
            'Use the graph to estimate blast radius, package precise context for AI, and keep the architecture map current while the repository changes.',
            ['ai:review-graph:impact', '--context', '--prompt', 'ai:review-graph:watch'],
        );

        $explanation = [
            'what' => 'Project Graph can estimate change impact, package focused architectural context, and keep the graph fresh during active work. This turns the graph from a passive map into an active safety layer for refactors and AI-assisted edits.',
            'how' => 'Use `ai:review-graph:impact` on a class, file, or node, optionally add `--context` for snippet packaging or `--prompt` for formatted AI prompts, and run `ai:review-graph:watch` when you want the graph to stay current while the codebase changes.',
            'why' => 'This is where the profit becomes obvious: fewer accidental blast-radius mistakes, less prompt bloat, faster review preparation, and a more disciplined path through risky changes.',
            'keywords' => [
                ['term' => 'ai:review-graph:impact', 'definition' => 'Analyzes downstream impact for a class, file path, or graph node and groups affected nodes by distance and module.'],
                ['term' => '--context', 'definition' => 'Adds focused source snippets and context packaging so the result can feed review or AI workflows directly.'],
                ['term' => '--prompt', 'definition' => 'Formats the context into a review, refactor, or test-oriented AI prompt scaffold.'],
                ['term' => 'ai:review-graph:watch', 'definition' => 'Polls for file changes and incrementally updates the graph so structural data stays current during active development.'],
            ],
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'project-graph',
                'currentSlug' => 'impact',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('project-graph')
            ->withSectionLabel('Project Graph')
            ->withSlug('impact')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('This is the shift from “interesting metadata” to “practical engineering safety”: impact analysis before edits, targeted context instead of giant prompts, and live graph freshness while work is in progress.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the impact workflow →')
            ->withDeepDiveLabel('How it reduces risky refactors and vague prompts →')
            ->withSourceCode([
                'Impact Commands' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Impact/Impact.example.sh'),
                'Prompt Packaging' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Impact/Prompt.example.md'),
                'Watch Mode' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Impact/Watch.example.sh'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Change Safety Flow',
                'title' => 'Check the blast radius before you edit, then package only the context you actually need',
                'summary' => 'This workflow is convenient because it reduces wasted work, but it is valuable because it reduces wrong work. Engineers and AI can start with the impact surface instead of discovering consequences after the patch is already half-written.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Analyze the impact radius',
                        'summary' => 'Point at a file path, FQCN, or node id and inspect affected modules and dependency depth before changing the target.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:impact Semitexa\\\\Demo\\\\Application\\\\Service\\\\DemoCatalogService',
                            'bin/semitexa ai:review-graph:impact packages/semitexa-demo/src/Application/Service/DemoCatalogService.php --json',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Package focused context for AI or review',
                        'summary' => 'Context output and prompt generation keep the AI input precise instead of dumping large random slices of the repository.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:impact Semitexa\\\\Demo\\\\Application\\\\Service\\\\DemoCatalogService --context',
                            'bin/semitexa ai:review-graph:impact Semitexa\\\\Demo\\\\Application\\\\Service\\\\DemoCatalogService --context --prompt=review',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Keep the graph current during active work',
                        'summary' => 'Watch mode reduces the chance that a long editing session drifts away from the graph that later commands depend on.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:watch --full-on-start',
                            'bin/semitexa ai:review-graph:watch --interval=2',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Where The Profit Shows Up',
                    'rules' => [
                        'Risky refactors become easier to scope before they start.',
                        'AI prompts become smaller, more relevant, and less hallucination-prone because context is selected structurally.',
                        'Review comments can be grounded in actual downstream impact instead of intuition alone.',
                        'Long work sessions stay safer because watch mode keeps the graph aligned with the current codebase.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Why It Matters',
                'title' => 'Impact and context packaging solve different but related problems',
                'summary' => 'One feature estimates what a change could touch. The other controls how much of that reality you expose to AI or reviewers.',
                'columns' => ['Problem', 'Project Graph response', 'Result'],
                'rows' => [
                    [
                        ['text' => 'I do not know the blast radius yet'],
                        ['text' => 'Run `ai:review-graph:impact` on the target'],
                        ['text' => 'Change planning starts with affected modules and dependency depth.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'My AI prompt is too broad'],
                        ['text' => 'Add `--context` or `--prompt`'],
                        ['text' => 'The prompt stays focused on impacted structure and snippets.', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'The graph may be stale while I edit'],
                        ['text' => 'Run `ai:review-graph:watch`'],
                        ['text' => 'Later graph-driven commands stay aligned with ongoing changes.', 'variant' => 'success'],
                    ],
                ],
                'paragraphs' => [
                    'This is a rare tooling surface that helps both cautious humans and aggressive AI workflows at the same time.',
                    'It does that by turning “what might this break?” and “what context should I include?” into explicit commands instead of improvised judgment every single time.',
                ],
                'note' => 'The deeper advantage is decision quality: smaller prompts, clearer scope, and fewer accidental side effects.',
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Recommended Habit',
                'title' => 'A disciplined graph-first change workflow',
                'summary' => 'Treat Project Graph as part of serious change preparation, not as an optional afterthought.',
                'rules' => [
                    'Refresh the graph before substantial edits so impact results reflect the current repository.',
                    'Run impact analysis before touching shared services, handlers, repositories, or framework infrastructure.',
                    'Prefer `--context` and prompt packaging over manually assembling giant AI prompts from arbitrary files.',
                    'Use watch mode during long-running refactors or review-fix sessions where the codebase changes repeatedly.',
                ],
            ])
            ->withRelatedPayloads([
                ['href' => '/demo/project-graph/overview', 'label' => 'Project Graph Overview'],
                ['href' => '/demo/project-graph/inspection', 'label' => 'Inspecting the Graph'],
            ])
            ->withExplanation($explanation);
    }
}
