<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Llm;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Llm\LlmOverviewPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: LlmOverviewPayload::class, resource: DemoFeatureResource::class)]
final class LlmOverviewHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(LlmOverviewPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'The `semitexa/llm` package gives a Semitexa project a governed AI surface: your own console commands can become discoverable skills instead of living behind ad-hoc prompt instructions.',
            'how' => 'You keep writing normal console commands. When one should be usable by the assistant, you add `#[AsAiSkill]` next to `#[AsCommand]`, choose the risk and argument policy, and let the manifest expose it to the LLM layer.',
            'why' => 'This keeps AI integration inside the framework contract. Teams extend the system by adding real commands and explicit metadata, not by teaching a model private tribal knowledge about the project.',
            'keywords' => [
                ['term' => '#[AsAiSkill]', 'definition' => 'Marks one of your commands as AI-discoverable and attaches execution metadata.'],
                ['term' => 'custom skills', 'definition' => 'Project-specific commands that your own Semitexa app chooses to expose to the assistant.'],
                ['term' => 'SkillManifest', 'definition' => 'The structured list of allowed skills exposed to the LLM layer.'],
                ['term' => 'policy-aware execution', 'definition' => 'Risk, confirmation, argument, and channel rules stay attached to each skill.'],
            ],
        ];

        return $resource
            ->pageTitle('LLM Module Overview — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'llm',
                'currentSlug' => 'overview',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('llm')
            ->withSectionLabel('LLM Module')
            ->withSlug('overview')
            ->withTitle('LLM Module Overview')
            ->withSummary('What `semitexa/llm` adds to the framework and how your project can expose its own CLI skills to the assistant.')
            ->withEntryLine('The LLM module is not “chat pasted onto a framework”. It gives your project a governed way to expose its own commands as AI-usable skills.')
            ->withHighlights(['#[AsAiSkill]', 'custom skills', 'SkillManifest', 'policy-aware execution'])
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
                    'Expose only the options an assistant should really control; do not default to “all arguments”.',
                    'After the first skill works, grow the surface intentionally instead of dumping the whole CLI into the manifest.',
                ],
            ])
            ->withExplanation($explanation);
    }
}
