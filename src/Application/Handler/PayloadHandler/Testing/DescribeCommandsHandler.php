<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Testing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Console\Command\ContractsListCommand;
use Semitexa\Core\Console\Command\LintHandlersCommand;
use Semitexa\Core\Console\Command\RoutesListCommand;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Testing\DescribeCommandsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Dev\Console\Command\AiAskCommand;
use Semitexa\Dev\Console\Command\DevGraph\DevGraphProjectCommand;
use Semitexa\Dev\Console\Command\DevGraph\DevGraphRouteCommand;

#[AsPayloadHandler(payload: DescribeCommandsPayload::class, resource: DemoFeatureResource::class)]
final class DescribeCommandsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(DescribeCommandsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'cli',
            slug: 'describe-commands',
            entryLine: 'A mature framework should explain itself under pressure. These commands turn route and container introspection into a first-class debugging surface.',
            learnMoreLabel: 'See the introspection workflow →',
            deepDiveLabel: 'How to use it in real debugging →',
            relatedSlugs: [],
            fallbackTitle: 'Project Graph Introspection',
            fallbackSummary: 'Routes, modules, contracts, and handlers can be introspected directly from the CLI instead of reverse-engineering the framework graph by hand.',
            fallbackHighlights: ['ai:ask', 'dev:graph:route', 'dev:graph:project', 'routes:list', 'contracts:list', 'semitexa:lint:*'],
            // NB: v1 intentionally queries explanation under 'cli/project-graph' rather than 'cli/describe-commands'.
            explanation: $this->explanationProvider->getExplanation('cli', 'project-graph') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'ai:ask Command' => $this->sourceCodeReader->readClassSource(AiAskCommand::class),
                'dev:graph:route Command' => $this->sourceCodeReader->readClassSource(DevGraphRouteCommand::class),
                'dev:graph:project Command' => $this->sourceCodeReader->readClassSource(DevGraphProjectCommand::class),
                'routes:list Command' => $this->sourceCodeReader->readClassSource(RoutesListCommand::class),
                'contracts:list Command' => $this->sourceCodeReader->readClassSource(ContractsListCommand::class),
                'semitexa:lint:handlers Command' => $this->sourceCodeReader->readClassSource(LintHandlersCommand::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/cli-command-workbench.html.twig', [
                'eyebrow' => 'Explain The System',
                'title' => 'Use the CLI to inspect the framework graph instead of guessing',
                'summary' => 'These commands turn route discovery, module structure, DI binding, and handler validation into explicit artifacts. That matters for both humans and AI operators when the codebase is large.',
                'pillars' => [
                    ['title' => 'Route chain visibility.', 'summary' => 'ai:ask route (backed by dev:graph:route) shows payload, handlers, resource, template, and auth posture for one endpoint.'],
                    ['title' => 'Project-level map.', 'summary' => 'ai:ask project and routes:list expose modules, counts, and discovered request surfaces.'],
                    ['title' => 'Binding and rule checks.', 'summary' => 'contracts:list and semitexa:lint:* help debug DI bindings and architectural invariants before runtime incidents.'],
                ],
                'commands' => [
                    ['name' => 'bin/semitexa ai:ask route --path=/demo/api/schema-discovery --json', 'purpose' => 'Explain the full execution chain for one route.', 'value' => 'Ideal when a page behaves unexpectedly and you need the exact payload → handler → resource path.'],
                    ['name' => 'bin/semitexa ai:ask project --json', 'purpose' => 'Emit a high-level project overview with modules, routes, and listeners.', 'value' => 'Useful for onboarding, architecture review, and AI navigation of unfamiliar projects.'],
                    ['name' => 'bin/semitexa routes:list --json', 'purpose' => 'List all discovered routes with source metadata.', 'value' => 'Gives a stable route inventory instead of relying on tribal knowledge.'],
                    ['name' => 'bin/semitexa contracts:list --json', 'purpose' => 'Show service contracts and their active implementation.', 'value' => 'Shortens DI debugging when multiple modules can satisfy the same interface.'],
                    ['name' => 'bin/semitexa semitexa:lint:handlers', 'purpose' => 'Validate handler signatures, bindings, and return types.', 'value' => 'Catches architecture drift before it becomes a runtime failure.'],
                ],
                'snippets' => [
                    ['label' => 'Inspect one route end to end', 'code' => "bin/semitexa ai:ask route --path=/demo/rendering/reactive-ai --method=GET\nbin/semitexa ai:ask route --path=/demo/rendering/reactive-ai --json"],
                    ['label' => 'Map the project and routes', 'code' => "bin/semitexa ai:ask project --json\nbin/semitexa routes:list --json"],
                    ['label' => 'Check DI and handler invariants', 'code' => "bin/semitexa contracts:list --json\nbin/semitexa semitexa:lint:handlers"],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Operational Payoff',
                'title' => 'Where these commands save real time',
                'summary' => 'The biggest gain is not convenience. It is shortening the distance between “something feels wrong” and “here is the exact part of the system that explains it.”',
                'rules' => [
                    'Reach for ai:ask route before you start manually tracing attributes across payloads, handlers, and resources.',
                    'Use routes:list and ai:ask project to orient both humans and agents in larger installations or modular monorepos.',
                    'Use contracts:list when interface resolution is ambiguous, especially in module override scenarios.',
                    'Run lints as architectural guardrails, not only as a last-minute CI formality.',
                ],
            ]);
    }
}
