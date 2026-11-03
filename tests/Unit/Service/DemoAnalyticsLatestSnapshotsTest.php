<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoAnalyticsSnapshotRepository;
use Semitexa\Demo\Application\Service\DemoAnalyticsAggregator;
use Semitexa\Demo\Domain\Model\DemoAnalyticsSnapshot;
use Semitexa\Orm\Domain\Model\ConnectionConfig;
use Semitexa\Orm\OrmManager;

/**
 * The reactive-analytics page shows the latest snapshot of each metric type.
 * It read them with one query per type (the ledger's requests.repeated-statements
 * named GET /demo/rendering/reactive-analytics for it); one query now.
 */
final class DemoAnalyticsLatestSnapshotsTest extends TestCase
{
    private OrmManager $orm;
    private DemoAnalyticsSnapshotRepository $snapshots;

    protected function setUp(): void
    {
        $this->orm = new OrmManager(config: new ConnectionConfig(driver: 'sqlite', sqliteMemory: true));
        $this->orm->getQueryAdapter()->execute('CREATE TABLE demo_analytics_snapshots (
            id BLOB PRIMARY KEY, tenant_id TEXT, metric_type TEXT NOT NULL, value TEXT NOT NULL,
            period_start TEXT, period_end TEXT, created_at TEXT, updated_at TEXT)');
        $this->snapshots = new DemoAnalyticsSnapshotRepository();
        (new \ReflectionProperty($this->snapshots, 'orm'))->setValue($this->snapshots, $this->orm);
    }

    protected function tearDown(): void
    {
        // The query log is process-wide: left on, it leaked into later tests
        // (RequestTracerTest saw it recording; release preflight 2026-11-03).
        $this->orm->disableQueryLog();
    }

    #[Test]
    public function the_latest_snapshot_of_every_type_is_read_in_one_query(): void
    {
        $this->snapshot('acme', 'pageviews', 100, '2026-11-03 10:00:00');
        $this->snapshot('acme', 'pageviews', 200, '2026-11-03 10:05:00');
        $this->snapshot('acme', 'conversions', 0.5, '2026-11-03 09:55:00');
        $this->snapshot('acme', 'conversions', 0.7, '2026-11-03 10:10:00');
        // Another tenant's later snapshot is not acme's latest.
        $this->snapshot('globex', 'pageviews', 999, '2026-11-03 11:00:00');
        $aggregator = new DemoAnalyticsAggregator();
        (new \ReflectionProperty($aggregator, 'snapshotRepository'))->setValue($aggregator, $this->snapshots);

        $this->orm->enableQueryLog();
        $latest = $aggregator->getLatestSnapshots('acme');
        $selects = array_filter(
            array_map(static fn (array $q): string => $q['sql'], $this->orm->drainQueryLog()),
            static fn (string $sql): bool => str_starts_with(ltrim($sql), 'SELECT'),
        );

        self::assertCount(1, $selects, 'one query for every type');
        self::assertSame(['pageviews', 'conversions', 'top_products'], array_keys($latest));
        self::assertSame(200.0, $latest['pageviews']?->getValue());
        self::assertSame(0.7, $latest['conversions']?->getValue());
        self::assertNull($latest['top_products'], 'a type with no snapshot');
    }

    private function snapshot(string $tenant, string $type, float $value, string $periodEnd): void
    {
        $end = new \DateTimeImmutable($periodEnd);
        $snapshot = new DemoAnalyticsSnapshot();
        $snapshot->setTenantId($tenant);
        $snapshot->setMetricType($type);
        $snapshot->setValue($value);
        $snapshot->setPeriodStart($end->modify('-5 minutes'));
        $snapshot->setPeriodEnd($end);
        $this->snapshots->save($snapshot);
    }
}
