<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Testing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Console\Command\QueueWorkCommand;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Testing\WorkersSchedulingPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Mail\Console\Command\MailWorkCommand;
use Semitexa\Scheduler\Console\SchedulerListCommand;
use Semitexa\Scheduler\Console\SchedulerPlanCommand;
use Semitexa\Scheduler\Console\SchedulerWorkCommand;
use Semitexa\Tenancy\CLI\TenantRunCommand;
use Semitexa\Webhooks\Console\WebhookReplayInboundCommand;
use Semitexa\Webhooks\Console\WebhookShowCommand;
use Semitexa\Webhooks\Console\WebhookWorkCommand;

#[AsPayloadHandler(payload: WorkersSchedulingPayload::class, resource: DemoFeatureResource::class)]
final class WorkersSchedulingHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(WorkersSchedulingPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'cli',
            slug: 'workers-scheduling',
            entryLine: 'Semitexa is not only request-response code. The CLI also owns the long-running workers and operator interventions that keep the platform moving.',
            learnMoreLabel: 'See the worker topology →',
            deepDiveLabel: 'Operational patterns behind the commands →',
            relatedSlugs: [],
            fallbackTitle: 'Workers & Scheduling',
            fallbackSummary: 'Run queues, scheduler pools, mail delivery, webhooks, and tenant-scoped commands from a coherent operator surface instead of bespoke daemons.',
            fallbackHighlights: ['queue:work', 'scheduler:list', 'scheduler:plan', 'scheduler:work', 'webhook:work', 'tenant:run'],
            explanation: $this->explanationProvider->getExplanation('cli', 'workers-scheduling') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'queue:work Command' => $this->sourceCodeReader->readClassSource(QueueWorkCommand::class),
                'scheduler:list Command' => $this->sourceCodeReader->readClassSource(SchedulerListCommand::class),
                'scheduler:plan Command' => $this->sourceCodeReader->readClassSource(SchedulerPlanCommand::class),
                'scheduler:work Command' => $this->sourceCodeReader->readClassSource(SchedulerWorkCommand::class),
                'webhook:show Command' => $this->sourceCodeReader->readClassSource(WebhookShowCommand::class),
                'webhook:replay:inbound Command' => $this->sourceCodeReader->readClassSource(WebhookReplayInboundCommand::class),
                'webhook:work Command' => $this->sourceCodeReader->readClassSource(WebhookWorkCommand::class),
                'mail:work Command' => $this->sourceCodeReader->readClassSource(MailWorkCommand::class),
                'tenant:run Command' => $this->sourceCodeReader->readClassSource(TenantRunCommand::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/cli-command-workbench.html.twig', [
                'eyebrow' => 'Operator Runtime',
                'title' => 'Long-running platform work is part of the same command surface',
                'summary' => 'Async queues, scheduler pools, outbound webhooks, mail delivery, and tenant-scoped execution all surface through explicit commands. That makes the runtime operable without custom one-off scripts.',
                'pillars' => [
                    ['title' => 'Dedicated workers.', 'summary' => 'queue:work, webhook:work, and mail:work turn background processing into explicit operator processes rather than hidden side-effects.'],
                    ['title' => 'Planner plus executor.', 'summary' => 'The scheduler surface is separated into list, plan, run-now, and work so teams can inspect and intervene before blindly starting daemons.'],
                    ['title' => 'Context-aware execution.', 'summary' => 'tenant:run lets operators execute commands inside a concrete tenant context instead of manually injecting environment assumptions.'],
                ],
                'commands' => [
                    ['name' => 'bin/semitexa queue:work nats async', 'purpose' => 'Run the async events worker against a chosen transport and queue.', 'value' => 'Keeps event-driven background work explicit and separately operable.'],
                    ['name' => 'bin/semitexa scheduler:list && bin/semitexa scheduler:plan', 'purpose' => 'Inspect configured schedules, then materialize due runs.', 'value' => 'Good operational sequence before starting or debugging the scheduler worker.'],
                    ['name' => 'bin/semitexa webhook:show outbox --status=pending && bin/semitexa webhook:work', 'purpose' => 'Inspect webhook backlog, then run delivery worker.', 'value' => 'Makes outbound integration behavior reviewable rather than opaque.'],
                    ['name' => 'bin/semitexa tenant:run acme cache:clear --twig', 'purpose' => 'Run a command inside a tenant context.', 'value' => 'Critical when the platform behavior depends on tenant-aware configuration or data isolation.'],
                ],
                'snippets' => [
                    ['label' => 'Bring up the scheduler loop deliberately', 'code' => "bin/semitexa scheduler:list\nbin/semitexa scheduler:plan\nbin/semitexa scheduler:work default"],
                    ['label' => 'Inspect and replay webhook traffic', 'code' => "bin/semitexa webhook:show inbox --limit=10\nbin/semitexa webhook:replay:inbound <delivery-uuid>"],
                    ['label' => 'Operate background work in one tenant', 'code' => "bin/semitexa tenant:run acme queue:work\nbin/semitexa tenant:run acme cache:clear --twig"],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Ops Pattern',
                'title' => 'Healthy command topology for background systems',
                'summary' => 'The value here is operability: separate inspect, plan, execute, and replay actions so the platform can be observed before it is pushed harder.',
                'rules' => [
                    'Separate “show/list” commands from “work/replay/run-now” commands so operators can inspect state before mutating it.',
                    'Treat workers as first-class processes with their own commands, not as accidental sidecars hidden behind the web runtime.',
                    'Use tenant:run when operational intent is tenant-specific instead of hoping ambient context is correct.',
                    'Scheduler surfaces are stronger when planning and execution remain explicit and individually observable.',
                ],
            ]);
    }
}
