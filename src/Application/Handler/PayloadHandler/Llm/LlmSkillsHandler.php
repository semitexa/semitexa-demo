<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Llm;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Llm\LlmSkillsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: LlmSkillsPayload::class, resource: DemoFeatureResource::class)]
final class LlmSkillsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(LlmSkillsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'llm',
            'skills',
            'Adding Skills',
            'How a console command becomes AI-executable through `#[AsAiSkill]`, metadata policy, and registry discovery.',
            ['#[AsAiSkill]', '#[AsCommand]', 'argumentPolicy', 'env::AI_ENABLE_*'],
        );

        return $resource
            ->pageTitle('Adding Skills — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'llm',
                'currentSlug' => 'skills',
                'infoWhat' => $presentation->summary,
                'infoHow' => null,
                'infoWhy' => null,
                'infoKeywords' => [],
            ])
            ->withSection('llm')
            ->withSectionLabel('LLM Module')
            ->withSlug('skills')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('A Semitexa skill is not a prompt trick. It is a normal console command with explicit AI metadata attached to it.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the skill authoring path →')
            ->withDeepDiveLabel('What metadata the registry extracts →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Skill Modes',
                'title' => 'Three useful ways to author skills',
                'summary' => 'The important choice is not only the command name. It is the execution mode around it: read-only, mutating, or env-controlled.',
                'codeSnippet' => <<<'PHP'
#[AsCommand(name: 'seed:demo-data', description: 'Seed demo data')]
#[AsAiSkill(
    allowed: 'env::AI_ENABLE_SEED_SKILL::false',
    summary: 'Seed local demo data only when explicitly enabled.',
    riskLevel: AiRiskLevel::High,
    confirmation: AiConfirmationMode::Always,
    argumentPolicy: 'allowlisted',
    exposeArguments: ['tenant', 'force'],
)]
final class SeedDemoDataCommand extends Command {}
PHP,
                'columns' => ['Mode', 'Typical metadata', 'Best for'],
                'rows' => [
                    [
                        ['text' => 'Read-only inspect skill'],
                        ['text' => 'Low risk + confirmation never + expose `--json`', 'code' => true],
                        ['text' => 'Safe introspection commands like DI inspection, diagnostics, listings, or status checks.'],
                    ],
                    [
                        ['text' => 'Mutating maintenance skill'],
                        ['text' => 'Medium/high risk + confirmation always + narrow allowlist', 'code' => true],
                        ['text' => 'Operations like cache clear, reindex, snapshot rebuild, or queue maintenance.'],
                    ],
                    [
                        ['text' => 'Env-controlled skill'],
                        ['text' => "allowed: 'env::AI_ENABLE_*::false'", 'code' => true],
                        ['text' => 'Commands you want available only in selected environments or temporary rollout windows.'],
                    ],
                    [
                        ['text' => 'Review pass'],
                        ['text' => 'bin/semitexa ai:skills --json', 'code' => true],
                        ['text' => 'Use the manifest output as the final review surface before you trust the assistant with the new skill.'],
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Authoring Rules',
                'title' => 'What makes a skill trustworthy',
                'summary' => 'The metadata is not decorative. It defines the execution contract the assistant must stay inside.',
                'rules' => [
                    'Keep `summary`, `useWhen`, and `avoidWhen` concrete so the planner can distinguish neighboring commands.',
                    'Set `riskLevel` and `confirmation` honestly, especially for mutating or destructive actions.',
                    'Prefer `argumentPolicy: allowlisted` and expose only the options an assistant should really touch.',
                    'Use `.env` gating for commands that should not be globally visible all the time.',
                    'After adding a skill, inspect `ai:skills --json` before trusting the assistant to use it.',
                ],
            ])
            ->withSourceCode([
                'Read-Only Skill Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Llm/ReadOnlySkill.example.php'),
                'Mutating Skill Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Llm/MutatingSkill.example.php'),
                'Env-Controlled Skill Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Llm/EnvControlledSkill.example.php'),
            ]);
    }
}
