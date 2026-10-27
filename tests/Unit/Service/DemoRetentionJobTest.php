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

    /** At most $limit (review): a limit of 0 still selected, and deleted, one row. */
    #[Test]
    public function a_limit_of_zero_deletes_nothing(): void
    {
        [$orm, $db, $runs] = self::database();
        $old = (new \DateTimeImmutable('-10 days'))->format('Y-m-d H:i:s');
        $db->execute("INSERT INTO demo_job_runs (id, job_type, status, created_at, updated_at) VALUES ('done-old', 'report_generation', 'completed', '{$old}', '{$old}')");
        $db->execute("INSERT INTO demo_analytics_snapshots VALUES ('s-old', '{$old}')");
        $snapshots = new DemoAnalyticsSnapshotRepository();
        (new \ReflectionProperty($snapshots, 'orm'))->setValue($snapshots, $orm);
        $cutoff = new \DateTimeImmutable('-1 day');

        self::assertSame(0, $runs->deleteFinishedBefore($cutoff, 0));
        self::assertSame(0, $snapshots->deleteEndedBefore($cutoff, 0));
        self::assertSame(['done-old'], array_column($db->execute('SELECT id FROM demo_job_runs')->rows, 'id'));
        self::assertSame(['s-old'], array_column($db->execute('SELECT id FROM demo_analytics_snapshots')->rows, 'id'));
    }

    /**
     * REGRESSION (review). save() between the SELECT and the DELETE made an
     * old run recent again, and the DELETE, checking the status alone,
     * removed it all the same.
     */
    #[Test]
    public function a_run_saved_after_it_was_selected_is_kept(): void
    {
        [, $db, $runs] = self::database();
        $run = new DemoJobRun();
        $run->setJobType('report_generation');
        $run->setStatus('completed');
        $id = $runs->save($run)->getId();
        $old = (new \DateTimeImmutable('-10 days'))->format('Y-m-d H:i:s');
        $db->execute('UPDATE demo_job_runs SET created_at = :old, updated_at = :old', ['old' => $old]);
        $cutoff = new \DateTimeImmutable('-1 day');

        $selected = (new \ReflectionMethod($runs, 'finishedIdsBefore'))->invoke($runs, $cutoff, 10);
        self::assertSame([Uuid7::toBytes($id)], $selected);
        // What save() writes, in between (save() itself would see no change within the same second).
        $db->execute('UPDATE demo_job_runs SET updated_at = :now', ['now' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')]);
        $deleted = (new \ReflectionMethod($runs, 'deleteFinishedIds'))->invoke($runs, $selected, $cutoff);

        self::assertSame(0, $deleted);
        self::assertSame([Uuid7::toBytes($id)], array_column($db->execute('SELECT id FROM demo_job_runs')->rows, 'id'));
    }

    /** @return array{OrmManager, \Semitexa\Orm\Adapter\DatabaseAdapterInterface, DemoJobRunRepository} */
    private static function database(): array
    {
        $orm = new OrmManager(config: new ConnectionConfig(driver: 'sqlite', sqliteMemory: true));
        $db = $orm->getQueryAdapter();
        $db->execute('CREATE TABLE demo_job_runs (id TEXT PRIMARY KEY, job_type TEXT, scheduler_run_id TEXT, status TEXT, progress_percent INTEGER, progress_message TEXT, result_payload TEXT, attempt_number INTEGER, created_at TEXT, updated_at TEXT)');
        $db->execute('CREATE TABLE demo_analytics_snapshots (id TEXT PRIMARY KEY, period_end TEXT)');
        $runs = new DemoJobRunRepository();
        (new \ReflectionProperty($runs, 'orm'))->setValue($runs, $orm);

        return [$orm, $db, $runs];
    }
}
