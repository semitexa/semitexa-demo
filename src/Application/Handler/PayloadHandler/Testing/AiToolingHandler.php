<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Testing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Service\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Service\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Testing\AiToolingPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Dev\Application\Console\Command\AiAskCommand;
use Semitexa\Dev\Application\Console\Command\AiObserveCommand;
use Semitexa\Dev\Application\Console\Command\AiOrientCommand;
use Semitexa\Dev\Application\Console\Command\AiVerifyCommand;
use Semitexa\Dev\Application\Console\Command\AiWorkCommand;

#[AsPayloadHandler(payload: AiToolingPayload::class, resource: DemoFeatureResource::class)]
final class AiToolingHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(AiToolingPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'cli',
            slug: 'ai-tooling',
            entryLine: 'Semitexa Dev gives people and coding agents one project-aware loop for orientation, structural inspection, runtime evidence, durable work memory, and precise verification.',
            learnMoreLabel: 'Follow the operating loop →',
            deepDiveLabel: 'See the complete command reference →',
            relatedSlugs: [],
            fallbackTitle: 'Semitexa Dev',
            fallbackSummary: 'Use the project-aware operating layer for orientation, planning, structural inspection, runtime debugging, durable work memory, and precise verification.',
            fallbackHighlights: ['ai:orient', 'ai:ask', 'ai:observe', 'ai:work', 'ai:verify'],
            explanation: $this->explanationProvider->getExplanation('cli', 'ai-tooling'),
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'ai:orient Command' => $this->sourceCodeReader->readClassSource(AiOrientCommand::class),
                'ai:ask Command' => $this->sourceCodeReader->readClassSource(AiAskCommand::class),
                'ai:observe Command' => $this->sourceCodeReader->readClassSource(AiObserveCommand::class),
                'ai:work Command' => $this->sourceCodeReader->readClassSource(AiWorkCommand::class),
                'ai:verify Command' => $this->sourceCodeReader->readClassSource(AiVerifyCommand::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/cli-command-workbench.html.twig', [
                'eyebrow' => 'Project-Aware Workflow',
                'title' => 'Start with facts, act on a narrow plan, and leave durable evidence',
                'summary' => 'Semitexa Dev connects repository state, structural discovery, live runtime traces, persistent work items, and diff-aware verification in one inspectable loop.',
                'pillars' => [
                    ['title' => 'Context before code.', 'summary' => 'orient, task, ask, context, and plan expose the current project shape and the safest implementation path.'],
                    ['title' => 'Runtime evidence.', 'summary' => 'The Observatory records process lifecycles, spans, queries, payload snapshots, source locations, and sandbox replays.'],
                    ['title' => 'Recoverable work.', 'summary' => 'epic, work, trace, and verify preserve decisions and prove the resulting change across sessions.'],
                ],
                'commands' => [
                    ['name' => 'bin/semitexa ai:orient --json', 'purpose' => 'Read Git state, active work, recent traces, and the last verification in one response.', 'value' => 'Starts a cold session from shared facts instead of repository archaeology.'],
                    ['name' => 'bin/semitexa ai:ask route --path=/invoices --json', 'purpose' => 'Resolve one route from payload through handler, resource, template, and access posture.', 'value' => 'Answers a structural question without broad file searches.'],
                    ['name' => 'bin/semitexa ai:observe show --id=p-123 --source', 'purpose' => 'Inspect one real process with timing, spans, queries, payload, and executed source.', 'value' => 'Turns runtime debugging into evidence instead of source-only guessing.'],
                    ['name' => 'bin/semitexa ai:work resume --id=tk-invoice-export --json', 'purpose' => 'Restore the task, its recent trace, and the exact next step.', 'value' => 'Lets work survive context compaction and session boundaries.'],
                    ['name' => 'bin/semitexa ai:verify --files=packages/semitexa-billing/src --json', 'purpose' => 'Select syntax, lint, structure, static-analysis, and test checks from the diff.', 'value' => 'Verifies the changed surface without running unrelated checks blindly.'],
                ],
                'snippets' => [
                    ['label' => 'Start a cold session', 'code' => "bin/semitexa ai:orient --json\nbin/semitexa ai:task 'add tenant-aware invoice export' --json"],
                    ['label' => 'Inspect before editing', 'code' => "bin/semitexa ai:ask route --path=/invoices --method=GET --json\nbin/semitexa ai:review-graph:impact 'App\\Billing\\InvoiceExporter' --json"],
                    ['label' => 'Observe and verify', 'code' => "bin/semitexa ai:observe tail --kind=http --follow --duration=15\nbin/semitexa ai:verify --files=packages/semitexa-billing/src --json"],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Operating Rules',
                'title' => 'What makes the workflow trustworthy',
                'summary' => 'The tools stay useful when each one answers a narrow question and reports its limits in a machine-readable form.',
                'rules' => [
                    'Use ai:ask and Project Graph before broad source searches when the question is structural.',
                    'Use ai:observe before forming a runtime hypothesis; the process journal records what actually ran.',
                    'Keep long work in epic, work, and trace artifacts with a concrete next step for the next session.',
                    'Run ai:verify after a coherent edit, then restart workers only when exercising the live server.',
                ],
            ]);
    }
}
