<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoAnalyticsSnapshotRepository;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;
use Semitexa\Demo\Application\Service\Scheduler\DemoRetentionJob;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Orm\Domain\Model\ConnectionConfig;
use Semitexa\Orm\Application\Service\Uuid7;
use Semitexa\Orm\OrmManager;
use Semitexa\Scheduler\Domain\Model\ScheduledJobContext;

/** The demo's jobs wrote rows nothing pruned: semitexa.com grew about 600 a day. */
final class DemoRetentionJobTest extends TestCase
{
    #[Test]
    public function old_finished_runs_and_old_snapshots_go_and_live_or_recent_ones_stay(): void
    {
        $orm = new OrmManager(config: new ConnectionConfig(driver: 'sqlite', sqliteMemory: true));
        $db = $orm->getQueryAdapter();
        $db->execute('CREATE TABLE demo_job_runs (id TEXT PRIMARY KEY, job_type TEXT, scheduler_run_id TEXT, status TEXT, progress_percent INTEGER, progress_message TEXT, result_payload TEXT, attempt_number INTEGER, created_at TEXT, updated_at TEXT)');
        $db->execute('CREATE TABLE demo_analytics_snapshots (id TEXT PRIMARY KEY, period_end TEXT)');
        $old = (new \DateTimeImmutable('-10 days'))->format('Y-m-d H:i:s');
        $new = (new \DateTimeImmutable('-1 hour'))->format('Y-m-d H:i:s');
        $runs = new DemoJobRunRepository();
        (new \ReflectionProperty($runs, 'orm'))->setValue($runs, $orm);
        // Through save(): rows written by hand carried times the real ones never had.
        $ids = [];
        foreach (['done-old' => 'completed', 'failed-old' => 'failed', 'running-old' => 'running', 'done-new' => 'completed'] as $name => $status) {
            $run = new DemoJobRun();
            $run->setJobType('report_generation');
            $run->setStatus($status);
            $ids[$name] = Uuid7::toBytes($runs->save($run)->getId());
            self::assertNotNull($run->getCreatedAt(), 'save() keeps the time');
        }
        $db->execute('UPDATE demo_job_runs SET created_at = :old, updated_at = :old WHERE id IN (:a, :b, :c)', ['old' => $old, 'a' => $ids['done-old'], 'b' => $ids['failed-old'], 'c' => $ids['running-old']]);
        // A row from before save() kept times: none at all.
        $db->execute("INSERT INTO demo_job_runs (id, job_type, status, progress_percent, attempt_number) VALUES ('legacy', 'report_generation', 'completed', 100, 1)");
        $db->execute("INSERT INTO demo_analytics_snapshots VALUES ('s-old', '{$old}'), ('s-new', '{$new}')");

        $job = new DemoRetentionJob();
        $snapshots = new DemoAnalyticsSnapshotRepository();
        (new \ReflectionProperty($snapshots, 'orm'))->setValue($snapshots, $orm);
        (new \ReflectionProperty($job, 'jobRuns'))->setValue($job, $runs);
        (new \ReflectionProperty($job, 'snapshots'))->setValue($job, $snapshots);
        $job->handle(new ScheduledJobContext(runId: 'r-1', jobClass: DemoRetentionJob::class, pool: 'default'));

        $left = array_column($db->execute('SELECT id FROM demo_job_runs')->rows, 'id');
        sort($left);
        $expected = [$ids['done-new'], $ids['running-old']];
        sort($expected);
        self::assertSame($expected, $left, 'a running row is a job\'s, whatever its age; a finished one with no time is old');
        self::assertSame(['s-new'], array_column($db->execute('SELECT id FROM demo_analytics_snapshots')->rows, 'id'));
    }
}
