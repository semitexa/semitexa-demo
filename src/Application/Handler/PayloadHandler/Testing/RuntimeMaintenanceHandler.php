<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Testing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Console\Command\CacheClearCommand;
use Semitexa\Core\Console\Command\LintHandlersCommand;
use Semitexa\Core\Console\Command\RegistrySyncCommand;
use Semitexa\Core\Console\Command\ServerReloadCommand;
use Semitexa\Core\Console\Command\TestHandlerCommand;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Testing\RuntimeMaintenancePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: RuntimeMaintenancePayload::class, resource: DemoFeatureResource::class)]
final class RuntimeMaintenanceHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(RuntimeMaintenancePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'cli',
            'runtime-maintenance',
            'Runtime Maintenance',
            'Reload workers, clear stale cache, sync registries, lint architecture rules, and probe handler wiring without reaching for ad-hoc shell scripts.',
            ['server:reload', 'cache:clear', 'registry:sync', 'semitexa:lint:*', 'test:handler'],
        );
        $explanation = $this->explanationProvider->getExplanation('cli', 'runtime-maintenance') ?? [];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'cli',
                'currentSlug' => 'runtime-maintenance',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('cli')
            ->withSlug('runtime-maintenance')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Strong CLI does not stop at code generation. It also gives operators and developers a disciplined way to refresh, validate, and diagnose a live Semitexa runtime.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the maintenance workflow →')
            ->withDeepDiveLabel('How to use this safely in practice →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/cli-command-workbench.html.twig', [
                'eyebrow' => 'Maintenance Surface',
                'title' => 'Refresh, validate, and inspect the runtime without improvisation',
                'summary' => 'These commands cover the maintenance loop that teams hit all the time: stale templates, generated registries, architecture drift, and suspicious DI wiring.',
                'pillars' => [
                    [
                        'title' => 'Graceful refresh.',
                        'summary' => 'server:reload picks up code changes without a full container restart when the Swoole runtime is healthy.',
                    ],
                    [
                        'title' => 'Architectural checks.',
                        'summary' => 'semitexa:lint:* and test:handler turn framework invariants into explicit checks instead of “I hope boot catches it.”',
                    ],
                    [
                        'title' => 'State hygiene.',
                        'summary' => 'cache:clear and registry:sync reduce the class of issues caused by stale compiled artifacts or outdated generated bindings.',
                    ],
                ],
                'commands' => [
                    [
                        'name' => 'bin/semitexa server:reload',
                        'purpose' => 'Gracefully reload Swoole workers after code changes.',
                        'value' => 'Fast path for normal development when containers do not need a full restart.',
                    ],
                    [
                        'name' => 'bin/semitexa cache:clear --twig',
                        'purpose' => 'Clear compiled Twig cache after template changes or stale render state.',
                        'value' => 'Cuts straight to one of the most common SSR debugging needs.',
                    ],
                    [
                        'name' => 'bin/semitexa registry:sync',
                        'purpose' => 'Regenerate DI-oriented registry artifacts such as contract resolvers.',
                        'value' => 'Keeps generated framework metadata aligned with current code.',
                    ],
                    [
                        'name' => 'bin/semitexa semitexa:lint:handlers',
                        'purpose' => 'Validate handler signatures and payload/resource bindings.',
                        'value' => 'Catches architecture drift before it shows up as a weird runtime failure.',
                    ],
                    [
                        'name' => 'bin/semitexa test:handler Semitexa\\\\Demo\\\\Application\\\\Handler\\\\PayloadHandler\\\\Testing\\\\AiToolingHandler',
                        'purpose' => 'Probe handler instantiation and property injection directly.',
                        'value' => 'Useful when DI uncertainty is more valuable to test than business behavior.',
                    ],
                ],
                'snippets' => [
                    [
                        'label' => 'Refresh changed templates safely',
                        'code' => "bin/semitexa cache:clear --twig\nbin/semitexa server:reload",
                    ],
                    [
                        'label' => 'Check architecture after refactor',
                        'code' => "bin/semitexa registry:sync\nbin/semitexa semitexa:lint:handlers\nbin/semitexa semitexa:lint:di",
                    ],
                    [
                        'label' => 'Probe one suspicious handler',
                        'code' => "bin/semitexa test:handler 'Semitexa\\\\Demo\\\\Application\\\\Handler\\\\PayloadHandler\\\\Testing\\\\RuntimeMaintenanceHandler'",
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Operator Habit',
                'title' => 'Good maintenance discipline',
                'summary' => 'The command surface is strongest when teams use it instead of ad-hoc deletes, grep, and trial-and-error restarts.',
                'rules' => [
                    'Prefer server:reload to full restarts when the problem is only code pickup and the runtime itself is healthy.',
                    'Reach for cache:clear when templates or compiled SSR output behave inconsistently before assuming deeper corruption.',
                    'Run lint and test:handler checks early during refactors, not only after breakage becomes visible in the browser.',
                    'Keep registry sync explicit so generated binding artifacts stay reviewable rather than magical.',
                ],
            ])
            ->withSourceCode([
                'server:reload Command' => $this->sourceCodeReader->readClassSource(ServerReloadCommand::class),
                'cache:clear Command' => $this->sourceCodeReader->readClassSource(CacheClearCommand::class),
                'registry:sync Command' => $this->sourceCodeReader->readClassSource(RegistrySyncCommand::class),
                'semitexa:lint:handlers Command' => $this->sourceCodeReader->readClassSource(LintHandlersCommand::class),
                'test:handler Command' => $this->sourceCodeReader->readClassSource(TestHandlerCommand::class),
            ])
            ->withExplanation($explanation);
    }
}
