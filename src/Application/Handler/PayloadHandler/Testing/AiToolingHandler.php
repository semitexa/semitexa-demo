<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Testing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Testing\AiToolingPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Dev\Console\Command\AiAskCommand;
use Semitexa\Dev\Console\Command\DevGraph\DevGraphCapabilitiesCommand;
use Semitexa\Dev\Console\Command\LogsAppCommand;
use Semitexa\Llm\Console\Command\AiAssistantCommand;
use Semitexa\Llm\Console\Command\AiSkillsCommand;

#[AsPayloadHandler(payload: AiToolingPayload::class, resource: DemoFeatureResource::class)]
final class AiToolingHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(AiToolingPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'cli',
            'ai-tooling',
            'AI Tooling Surface',
            'Semitexa exposes AI-facing commands as explicit CLI contracts: capabilities, skills, log access, and a local assistant entrypoint.',
            ['ai:ask', 'ai:skills', 'logs:app', 'ai', '--json'],
        );
        $explanation = $this->explanationProvider->getExplanation('cli', 'ai-tooling') ?? [];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'cli',
                'currentSlug' => 'ai-tooling',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('cli')
            ->withSlug('ai-tooling')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('If the framework wants to be AI-native, the console surface has to be machine-readable and operationally safe, not just human-friendly.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the AI command surface →')
            ->withDeepDiveLabel('What makes it agent-friendly →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/cli-command-workbench.html.twig', [
                'eyebrow' => 'Agent Operations',
                'title' => 'The CLI explains what an AI agent may do before it does it',
                'summary' => 'Capabilities and skills can be exported as manifests, logs can be queried in structured form, and the local assistant has a first-class entrypoint instead of being bolted on as a hidden dev script.',
                'pillars' => [
                    [
                        'title' => 'Capability manifest.',
                        'summary' => 'ai:ask capabilities lists generator and introspection commands with intended use, required inputs, and avoid-when guidance.',
                    ],
                    [
                        'title' => 'Skill registry.',
                        'summary' => 'ai:skills exposes risk, confirmation mode, dry-run support, and inputs so AI orchestration can stay explicit.',
                    ],
                    [
                        'title' => 'LLM-friendly operations.',
                        'summary' => 'logs:app and JSON output modes let agents inspect the system without brittle terminal scraping.',
                    ],
                ],
                'commands' => [
                    [
                        'name' => 'bin/semitexa ai:ask capabilities --json',
                        'purpose' => 'Export the command capability manifest for generators and structured tooling.',
                        'value' => 'Lets agents choose the right command with explicit input/output metadata.',
                    ],
                    [
                        'name' => 'bin/semitexa ai:skills --json',
                        'purpose' => 'Export AI-executable skills with risk and confirmation policy.',
                        'value' => 'Makes agent permissions and affordances reviewable instead of implicit.',
                    ],
                    [
                        'name' => 'bin/semitexa logs:app --file app --since -15m --json',
                        'purpose' => 'Query recent application logs in a stable structured format.',
                        'value' => 'Cuts down hallucinated debugging because the agent can inspect recent evidence directly.',
                    ],
                    [
                        'name' => 'bin/semitexa ai',
                        'purpose' => 'Open the local assistant entrypoint backed by the registered skill surface.',
                        'value' => 'Turns the framework itself into an operator-facing AI console rather than a bundle of disconnected helpers.',
                    ],
                ],
                'snippets' => [
                    [
                        'label' => 'Export machine-readable capability metadata',
                        'code' => "bin/semitexa ai:ask capabilities --json\nbin/semitexa ai:skills --json",
                    ],
                    [
                        'label' => 'Inspect app logs without grep gymnastics',
                        'code' => "bin/semitexa logs:app --file app --since -15m --grep tenant --json",
                    ],
                    [
                        'label' => 'Use the local assistant entrypoint',
                        'code' => "bin/semitexa ai\n# interactive assistant backed by the registered skill manifest",
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Design Rule',
                'title' => 'What AI-ready CLI should look like',
                'summary' => 'The important part is not “AI branding”. The important part is that the framework exposes stable, machine-readable operational seams.',
                'rules' => [
                    'If a command is meant to be used by an agent, give it a JSON mode and explicit input semantics.',
                    'If a command may be risky, expose confirmation and dry-run policy as metadata rather than burying it in documentation.',
                    'Logs and project introspection should be queryable without forcing the agent to scrape arbitrary terminal prose.',
                    'A local assistant is useful only when the surrounding command surface is disciplined and discoverable.',
                ],
            ])
            ->withSourceCode([
                'ai:ask Command' => $this->sourceCodeReader->readClassSource(AiAskCommand::class),
                'dev:graph:capabilities Command' => $this->sourceCodeReader->readClassSource(DevGraphCapabilitiesCommand::class),
                'ai:skills Command' => $this->sourceCodeReader->readClassSource(AiSkillsCommand::class),
                'ai Command' => $this->sourceCodeReader->readClassSource(AiAssistantCommand::class),
                'logs:app Command' => $this->sourceCodeReader->readClassSource(LogsAppCommand::class),
            ])
            ->withExplanation($explanation);
    }
}
