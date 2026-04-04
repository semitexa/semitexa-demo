<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Testing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Testing\OrmConsolePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Orm\Console\Command\OrmDiffCommand;
use Semitexa\Orm\Console\Command\OrmSeedCommand;
use Semitexa\Orm\Console\Command\OrmStatusCommand;
use Semitexa\Orm\Console\Command\OrmSyncCommand;

#[AsPayloadHandler(payload: OrmConsolePayload::class, resource: DemoFeatureResource::class)]
final class OrmConsoleHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(OrmConsolePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('cli', 'orm-console') ?? [];

        return $resource
            ->pageTitle('ORM Console Toolkit — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'cli',
                'currentSlug' => 'orm-console',
                'infoWhat' => $explanation['what'] ?? 'The ORM ships with a command surface for inspecting, diffing, syncing, and seeding schema safely.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('cli')
            ->withSlug('orm-console')
            ->withTitle('ORM Console Toolkit')
            ->withSummary('The ORM ships with a practical CLI surface: status, diff, sync, and seed commands with dry-run safety and SQL plan export.')
            ->withEntryLine('Framework credibility also lives in operations. The ORM CLI should tell you what will change before it changes anything.')
            ->withHighlights(['orm:status', 'orm:diff', 'orm:sync', 'orm:seed', '--output'])
            ->withLearnMoreLabel('See the command surface →')
            ->withDeepDiveLabel('What each command is for →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/orm-console-toolkit.html.twig', [
                'commands' => [
                    [
                        'name' => 'bin/semitexa orm:status',
                        'purpose' => 'Show DB/server capabilities and whether schema is in sync.',
                        'value' => 'Gives fast operational context before any change.',
                    ],
                    [
                        'name' => 'bin/semitexa orm:diff',
                        'purpose' => 'List code-vs-database differences.',
                        'value' => 'Lets reviewers see pending table, column, index, and FK changes.',
                    ],
                    [
                        'name' => 'bin/semitexa orm:sync --dry-run',
                        'purpose' => 'Build the execution plan without executing it.',
                        'value' => 'Safe default for CI, review, and local inspection.',
                    ],
                    [
                        'name' => 'bin/semitexa orm:sync --output plan.sql',
                        'purpose' => 'Export the SQL plan to a file.',
                        'value' => 'Useful for audit trails and DevOps handoff.',
                    ],
                    [
                        'name' => 'bin/semitexa orm:seed',
                        'purpose' => 'Run defaults() upserts for seedable resources.',
                        'value' => 'Makes local/demo environments reproducible quickly.',
                    ],
                ],
                'snippets' => [
                    [
                        'label' => 'Inspect current sync state',
                        'code' => "bin/semitexa orm:status\nbin/semitexa orm:diff",
                    ],
                    [
                        'label' => 'Review SQL before execution',
                        'code' => "bin/semitexa orm:sync --dry-run -vv\nbin/semitexa orm:sync --dry-run --output var/migrations/history/review.sql",
                    ],
                    [
                        'label' => 'Apply safe changes, then seed',
                        'code' => "bin/semitexa orm:sync\nbin/semitexa orm:seed",
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/orm-console-rules.html.twig', [
                'rules' => [
                    'Start with orm:status or orm:diff when you need to understand schema state quickly.',
                    'Treat orm:sync --dry-run as the normal review path, not as an exotic extra flag.',
                    'Use --allow-destructive only when the team intentionally wants to include DROP and narrowing operations.',
                    'Export plans with --output when another human or deployment system needs the exact SQL artifact.',
                ],
            ])
            ->withSourceCode([
                'orm:status Command' => $this->sourceCodeReader->readClassSource(OrmStatusCommand::class),
                'orm:diff Command' => $this->sourceCodeReader->readClassSource(OrmDiffCommand::class),
                'orm:sync Command' => $this->sourceCodeReader->readClassSource(OrmSyncCommand::class),
                'orm:seed Command' => $this->sourceCodeReader->readClassSource(OrmSeedCommand::class),
            ])
            ->withExplanation($explanation);
    }
}
