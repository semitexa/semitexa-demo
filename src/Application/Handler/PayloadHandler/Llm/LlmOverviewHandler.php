<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Llm;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Llm\LlmOverviewPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;

#[AsPayloadHandler(payload: LlmOverviewPayload::class, resource: DemoFeatureResource::class)]
final class LlmOverviewHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(LlmOverviewPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'llm',
            'overview',
            'LLM Module Overview',
            'What `semitexa/llm` adds to the framework and how your project can expose its own CLI skills to the assistant.',
            ['#[AsAiSkill]', 'custom skills', 'SkillManifest', 'policy-aware execution'],
        );

        return $resource
            ->pageTitle('LLM Module Overview — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'llm',
                'currentSlug' => 'overview',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('llm')
            ->withSectionLabel('LLM Module')
            ->withSlug('overview')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('The LLM module is not "chat pasted onto a framework". It gives your project a governed way to expose its own commands as AI-usable skills.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the module surface →')
            ->withDeepDiveLabel('Which moving parts matter first →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/cli-command-workbench.html.twig', [
                'eyebrow' => 'Project Extension',
                'title' => 'Turn your own commands into skills',
                'summary' => 'The point of `semitexa/llm` is not only to ship one assistant command. The point is to let each project expose its own safe command surface to the assistant through explicit skill metadata.',
                'pillars' => [
                    [
                        'title' => 'Your commands stay primary.',
                        'summary' => 'A skill starts as an ordinary Semitexa console command owned by your project or package.',
                    ],
                    [
                        'title' => 'Metadata makes it usable.',
                        'summary' => '`#[AsAiSkill]` adds summary, use/avoid guidance, confirmation mode, and argument policy.',
                    ],
                    [
                        'title' => 'The manifest stays reviewable.',
                        'summary' => 'Only the commands you expose become part of the skill manifest the assistant may use.',
                    ],
                ],
                'commands' => [
                    [
                        'name' => '#[AsCommand(...)]',
                        'purpose' => 'Define the real console command first.',
                        'value' => 'The assistant extends your CLI; it does not replace it with a second hidden automation layer.',
                    ],
                    [
                        'name' => '#[AsAiSkill(...)]',
                        'purpose' => 'Attach AI metadata to the command you want to expose.',
                        'value' => 'This is where you define risk, confirmation, exposed arguments, and usage guidance.',
                    ],
                    [
                        'name' => 'bin/semitexa ai:skills --json',
                        'purpose' => 'Verify what the assistant can actually see.',
                        'value' => 'Treat the manifest as the review surface for your project-specific skills.',
                    ],
                ],
                'snippets' => [
                    [
                        'label' => 'Minimal custom skill',
                        'code' => "#[AsCommand(name: 'reports:rebuild')]\n#[AsAiSkill(summary: 'Rebuild reporting snapshots', argumentPolicy: 'allowlisted')]\nfinal class RebuildReportsCommand extends Command {}",
                    ],
                    [
                        'label' => 'Review the exposed manifest',
                        'code' => "bin/semitexa ai:skills --json",
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Own Skills',
                'title' => 'Good first examples for your project',
                'summary' => 'The best first skills are concrete operational or introspection commands that already have a clear CLI contract.',
                'rules' => [
                    'Start with safe read-only or low-blast-radius commands such as diagnostics, cache hygiene, index refresh, or report rebuilds.',
                    'Give each skill a strong `useWhen` and `avoidWhen` so neighboring commands do not blur together in planning.',
                    'Expose only the options an assistant should really control; do not default to "all arguments".',
                    'After the first skill works, grow the surface intentionally instead of dumping the whole CLI into the manifest.',
                ],
            ]);
    }
}
