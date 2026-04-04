<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Data\SchemaSyncPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Orm\Console\Command\OrmSyncCommand;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Sync\AuditLogger;
use Semitexa\Orm\Sync\SyncEngine;

#[AsPayloadHandler(payload: SchemaSyncPayload::class, resource: DemoFeatureResource::class)]
final class SchemaSyncHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(SchemaSyncPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('data', 'schema-sync') ?? [];

        return $resource
            ->pageTitle('Schema Sync, Not Migration Churn — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'schema-sync',
                'infoWhat' => $explanation['what'] ?? 'The ORM computes schema changes from code and database state instead of forcing constant manual migration churn.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('schema-sync')
            ->withTitle('Schema Sync, Not Migration Churn')
            ->withSummary('Semitexa creates SQL only when the real schema changed, blocks destructive drops by default, and logs the exact DDL plan as SQL and JSON.')
            ->withEntryLine('You do not hand-write busywork migrations all day. The ORM derives the plan, blocks dangerous drops by default, and records the exact SQL it ran.')
            ->withHighlights(['orm:sync', '--dry-run', '--allow-destructive', 'two-phase drop', 'AuditLogger'])
            ->withLearnMoreLabel('See the sync plan →')
            ->withDeepDiveLabel('Why destructive changes are delayed →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/schema-sync-showcase.html.twig', [
                'painPoints' => [
                    'Teams waste time writing empty or obvious migrations just to mirror what the code already says.',
                    'Column drops are dangerous when one careless migration can erase data immediately.',
                    'Ops often needs the actual SQL plan, not a hand-wavy promise that the framework will figure it out.',
                ],
                'stats' => [
                    ['value' => '1', 'label' => 'command to compare code and DB'],
                    ['value' => '2', 'label' => 'phases before a destructive drop completes'],
                    ['value' => '2', 'label' => 'audit outputs written per sync run (.json + .sql)'],
                ],
                'phases' => [
                    [
                        'badge' => 'Phase 1',
                        'title' => 'Mark deprecated, do not drop yet',
                        'tone' => 'base',
                        'summary' => 'If a column disappears from code, the ORM first marks it deprecated instead of deleting it immediately.',
                        'chips' => ['safe operation', 'comment marker', 'review window'],
                    ],
                    [
                        'badge' => 'Phase 2',
                        'title' => 'Drop only when explicitly allowed',
                        'tone' => 'warning',
                        'summary' => 'A later sync can perform the actual DROP, and only when destructive operations are explicitly allowed.',
                        'chips' => ['DROP COLUMN', '--allow-destructive', 'intentional action'],
                    ],
                ],
                'snippets' => [
                    [
                        'label' => 'Dry-run sync plan',
                        'code' => "bin/semitexa orm:sync --dry-run\n\nSafe operations: 3\nDestructive operations: 1 (require --allow-destructive)",
                    ],
                    [
                        'label' => 'Export SQL plan',
                        'code' => "bin/semitexa orm:sync --dry-run --output var/migrations/history/plan.sql",
                    ],
                    [
                        'label' => 'Audit files written after real sync',
                        'code' => "var/migrations/history/2026-03-27_12-14-03.184_sync.json\nvar/migrations/history/2026-03-27_12-14-03.184_sync.sql",
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/schema-sync-rules.html.twig', [
                'rules' => [
                    'A missing column does not become an immediate DROP; the first pass only marks it deprecated.',
                    'Real destructive operations are separated in the execution plan and require explicit opt-in with --allow-destructive.',
                    'The executed plan is logged as both structured JSON and plain SQL for review, audit, and DevOps handoff.',
                    'If code and database already match, there is nothing to write and nothing to execute.',
                ],
            ])
            ->withSourceCode([
                'orm:sync Command' => $this->sourceCodeReader->readClassSource(OrmSyncCommand::class),
                'SyncEngine' => $this->sourceCodeReader->readClassSource(SyncEngine::class),
                'AuditLogger' => $this->sourceCodeReader->readClassSource(AuditLogger::class),
                'OrmManager' => $this->sourceCodeReader->readClassSource(OrmManager::class),
            ])
            ->withExplanation($explanation);
    }
}
