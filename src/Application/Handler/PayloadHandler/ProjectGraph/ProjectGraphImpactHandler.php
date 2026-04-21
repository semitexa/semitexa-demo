<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\ProjectGraph;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\ProjectGraph\ProjectGraphImpactPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProjectGraphImpactPayload::class, resource: DemoFeatureResource::class)]
final class ProjectGraphImpactHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ProjectGraphImpactPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'project-graph',
            slug: 'impact',
            entryLine: 'This is the shift from structural knowledge to structural safety: impact before edits, focused context instead of giant prompts, and graph freshness during long work sessions.',
            learnMoreLabel: 'See the impact workflow →',
            deepDiveLabel: 'How it reduces risky refactors and vague prompts →',
            relatedSlugs: ['overview', 'inspection'],
            fallbackTitle: 'Impact, Context, and Watch Mode',
            fallbackSummary: 'Use impact analysis, context packing, and watch mode to scope risky changes and keep graph-backed answers current during long work sessions.',
            fallbackHighlights: ['ai:review-graph:impact', '--context', '--prompt', 'ai:review-graph:watch'],
            explanation: [
                'what' => 'Impact mode is where Project Graph becomes a change-safety tool. It estimates blast radius, groups affected modules by depth, and can package only the snippets and context that actually belong in a review or AI prompt.',
                'how' => 'Use `ai:review-graph:impact` on a class, file path, or node id, add `--context` when you want a focused context package, add `--prompt` when that package should be shaped for review or refactor work, and use `ai:review-graph:watch` when a long session would otherwise leave graph-backed answers stale.',
                'why' => 'This matters because risky work usually fails before the patch is done: teams underestimate blast radius, overfeed AI prompts, and keep editing while structural assumptions drift. Project Graph makes those failure modes explicit and reviewable.',
                'keywords' => [
                    ['term' => 'ai:review-graph:impact', 'definition' => 'Analyzes downstream impact for a class, file path, or node id and groups affected nodes by depth and module.'],
                    ['term' => '--context', 'definition' => 'Packages focused source snippets and graph-backed context so review or AI work starts from the impacted structure instead of random file dumps.'],
                    ['term' => '--prompt', 'definition' => 'Formats the context package into a review-, refactor-, or test-oriented prompt scaffold.'],
                    ['term' => 'ai:review-graph:watch', 'definition' => 'Keeps the stored graph fresh during active development so later graph-backed answers match the current codebase.'],
                ],
            ],
            pageTitleSuffix: ' — Semitexa Demo',
            sectionLabel: 'Project Graph',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Impact Commands' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Impact/Impact.example.sh'),
                'Prompt Packaging' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Impact/Prompt.example.md'),
                'Watch Mode' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/ProjectGraph/Impact/Watch.example.sh'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Change Safety Flow',
                'title' => 'Check the blast radius before you edit, then package only the context you actually need',
                'summary' => 'This matters because the expensive mistakes usually happen before the final patch exists: the blast radius was guessed, the prompt was too broad, or the graph-backed answer was already stale. Impact mode puts those risks on the surface early.',
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
                        'summary' => 'Watch mode is useful when a long editing or review-fix session would otherwise drift away from the graph that later impact answers depend on.',
                        'commands' => [
                            'bin/semitexa ai:review-graph:watch --full-on-start',
                            'bin/semitexa ai:review-graph:watch --interval=2',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Where The Profit Shows Up',
                    'rules' => [
                        'Risky refactors become easier to scope before they start instead of after the first surprising breakage.',
                        'AI prompts become smaller and more defensible because context is selected from the impacted structure.',
                        'Review comments can be grounded in downstream effect instead of intuition alone.',
                        'Long work sessions stay safer because watch mode keeps graph-backed answers aligned with the changing repository.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Why It Matters',
                'title' => 'Impact and context packaging solve different but related problems',
                'summary' => 'One feature estimates what a change could touch. The other controls how much of that reality you expose to AI or reviewers.',
                'columns' => ['Problem', 'Project Graph response', 'Result'],
                'rows' => [
                    [['text' => 'I do not know the blast radius yet'], ['text' => 'Run `ai:review-graph:impact` on the target'], ['text' => 'Change planning starts with affected modules and dependency depth.', 'variant' => 'success']],
                    [['text' => 'My AI prompt is too broad'], ['text' => 'Add `--context` or `--prompt`'], ['text' => 'The prompt stays focused on impacted structure and snippets.', 'variant' => 'success']],
                    [['text' => 'The graph may be stale while I edit'], ['text' => 'Run `ai:review-graph:watch`'], ['text' => 'Later graph-driven commands stay aligned with ongoing changes.', 'variant' => 'success']],
                ],
                'paragraphs' => [
                    'This is one of the package surfaces where humans and AI benefit from exactly the same discipline.',
                    'It turns “what might this break?” and “what context actually belongs here?” into explicit, reviewable commands instead of improvised judgment.',
                ],
                'note' => 'The deeper advantage is decision quality: clearer scope, smaller prompts, and fewer accidental side effects.',
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Recommended Habit',
                'title' => 'A disciplined graph-first change workflow',
                'summary' => 'Treat Project Graph as part of serious change preparation, not as an optional afterthought.',
                'rules' => [
                    'Refresh or verify the graph before substantial edits when impact results need to reflect the current repository state.',
                    'Run impact analysis before touching shared services, handlers, repositories, or framework infrastructure.',
                    'Prefer `--context` and prompt packaging over manually assembling giant AI prompts from arbitrary files.',
                    'Use watch mode during long-running refactors or review-fix sessions where the codebase changes repeatedly.',
                ],
            ]);
    }
}
