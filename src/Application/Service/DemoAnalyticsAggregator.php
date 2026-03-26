<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAnalyticsSnapshotResource;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoAnalyticsSnapshotRepository;

#[AsService]
final class DemoAnalyticsAggregator
{
    private const METRIC_TYPES = ['pageviews', 'conversions', 'top_products'];

    #[InjectAsReadonly]
    protected ?DemoAnalyticsSnapshotRepository $snapshotRepository = null;

    /**
     * Record a simulated analytics snapshot for a given metric type.
     */
    public function recordSnapshot(string $metricType, string $tenantId = 'acme'): void
    {
        $periodEnd = new \DateTimeImmutable();

        $snapshot = new DemoAnalyticsSnapshotResource();
        $snapshot->metric_type = $metricType;
        $snapshot->tenant_id = $tenantId;
        $snapshot->value = $this->generateValue($metricType);
        $snapshot->period_end = $periodEnd;
        $snapshot->period_start = $periodEnd->modify('-5 minutes');

        $this->snapshotRepository?->save($snapshot);
    }

    /**
     * Get the latest snapshots for all metric types.
     *
     * @return array<string, mixed>
     */
    public function getLatestSnapshots(string $tenantId = 'acme'): array
    {
        $result = [];
        foreach (self::METRIC_TYPES as $type) {
            $snapshots = $this->snapshotRepository?->findByMetricAndTenant($type, $tenantId, 1) ?? [];
            $result[$type] = $snapshots[0] ?? null;
        }
        return $result;
    }

    public function getMetricTypes(): array
    {
        return self::METRIC_TYPES;
    }

    private function generateValue(string $metricType): float
    {
        return match ($metricType) {
            'pageviews'    => (float) random_int(800, 2400),
            'conversions'  => round(random_int(20, 120) / 1000, 4),
            'top_products' => (float) random_int(10, 50),
            default        => 0.0,
        };
    }
}
