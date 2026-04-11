<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

#[AsService]
final class DemoLedgerInspector
{
    private const DEMO_DOMAIN = 'demo';
    private const DEMO_EVENT_TYPES = ['demo_item_created', 'demo_notification_event'];

    /**
     * @return array{
     *   configured: bool,
     *   enabled: bool,
     *   path: string,
     *   fileExists: bool,
     *   error: string|null,
     *   stats: list<array{label: string, value: string}>,
     *   rows: list<array<int, array{text: string, variant?: string, code?: bool}>>
     * }
     */
    public function inspect(int $limit = 8): array
    {
        $limit = max(1, min($limit, 25));
        $path = $this->resolveLedgerPath();
        $configured = $this->isConfigured();
        $enabled = $this->isEnabled();
        $fileExists = is_file($path);

        $result = [
            'configured' => $configured,
            'enabled' => $enabled,
            'path' => $this->toProjectRelativePath($path),
            'fileExists' => $fileExists,
            'error' => null,
            'stats' => [
                ['label' => 'Runtime', 'value' => $enabled ? 'enabled' : 'disabled'],
                ['label' => 'Configured', 'value' => $configured ? 'yes' : 'no'],
                ['label' => 'Ledger file', 'value' => $fileExists ? 'present' : 'missing'],
            ],
            'rows' => [],
        ];

        if (!$fileExists) {
            return $result;
        }

        $db = null;

        try {
            $db = new \SQLite3($path, SQLITE3_OPEN_READONLY);
            $db->enableExceptions(true);

            $total = $this->fetchScalar(
                $db,
                'SELECT COUNT(*) FROM events WHERE domain = :domain AND event_type IN (:item_created, :notification)',
                [
                    ':domain' => self::DEMO_DOMAIN,
                    ':item_created' => self::DEMO_EVENT_TYPES[0],
                    ':notification' => self::DEMO_EVENT_TYPES[1],
                ],
            );
            $pending = $this->fetchScalar(
                $db,
                "SELECT COUNT(*) FROM events WHERE domain = :domain AND publish_status = 'pending' AND event_type IN (:item_created, :notification)",
                [
                    ':domain' => self::DEMO_DOMAIN,
                    ':item_created' => self::DEMO_EVENT_TYPES[0],
                    ':notification' => self::DEMO_EVENT_TYPES[1],
                ],
            );
            $latestCreatedAt = $this->fetchScalar(
                $db,
                'SELECT created_at FROM events WHERE domain = :domain AND event_type IN (:item_created, :notification) ORDER BY sequence DESC LIMIT 1',
                [
                    ':domain' => self::DEMO_DOMAIN,
                    ':item_created' => self::DEMO_EVENT_TYPES[0],
                    ':notification' => self::DEMO_EVENT_TYPES[1],
                ],
            );

            $result['stats'] = [
                ['label' => 'Runtime', 'value' => $enabled ? 'enabled' : 'disabled'],
                ['label' => 'Demo events', 'value' => (string) $total],
                ['label' => 'Pending publish', 'value' => (string) $pending],
                ['label' => 'Last append', 'value' => $latestCreatedAt !== null ? (string) $latestCreatedAt : 'none'],
            ];

            $stmt = $db->prepare(
                'SELECT sequence, event_type, source, publish_status, created_at
                 FROM events
                 WHERE domain = :domain
                   AND event_type IN (:item_created, :notification)
                 ORDER BY sequence DESC
                 LIMIT :limit'
            );
            $stmt->bindValue(':domain', self::DEMO_DOMAIN, SQLITE3_TEXT);
            $stmt->bindValue(':item_created', self::DEMO_EVENT_TYPES[0], SQLITE3_TEXT);
            $stmt->bindValue(':notification', self::DEMO_EVENT_TYPES[1], SQLITE3_TEXT);
            $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
            $query = $stmt->execute();

            while (($row = $query->fetchArray(SQLITE3_ASSOC)) !== false) {
                $status = (string) ($row['publish_status'] ?? 'unknown');

                $result['rows'][] = [
                    ['text' => '#' . (string) $row['sequence'], 'code' => true],
                    ['text' => (string) $row['event_type'], 'code' => true],
                    ['text' => (string) $row['source']],
                    ['text' => $status, 'variant' => $this->variantForStatus($status)],
                    ['text' => (string) $row['created_at']],
                ];
            }
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        } finally {
            $db?->close();
        }

        return $result;
    }

    public function isEnabled(): bool
    {
        $enabled = getenv('LEDGER_ENABLED');

        if ($enabled !== false && $enabled !== '') {
            $normalized = strtolower(trim((string) $enabled));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return $this->hasEnv('LEDGER_NODE_ID')
            && $this->hasEnv('LEDGER_HMAC_KEY')
            && ($this->hasEnv('NATS_URL') || $this->hasEnv('NATS_PRIMARY_URL'));
    }

    public function getDisplayPath(): string
    {
        return $this->toProjectRelativePath($this->resolveLedgerPath());
    }

    private function hasEnv(string $key): bool
    {
        $value = getenv($key);

        return $value !== false && trim((string) $value) !== '';
    }

    private function resolveLedgerPath(): string
    {
        $configuredPath = getenv('LEDGER_DB_PATH');
        if ($configuredPath !== false && trim((string) $configuredPath) !== '') {
            $path = trim((string) $configuredPath);
        } else {
            $nodeId = getenv('LEDGER_NODE_ID');
            $path = $nodeId !== false && trim((string) $nodeId) !== ''
                ? '/var/lib/semitexa/ledger/' . trim((string) $nodeId) . '.sqlite'
                : 'var/ledger/ledger.sqlite';
        }

        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->projectRoot() . '/' . ltrim($path, '/');
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:\\\\/', $path) === 1;
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 5);
    }

    private function toProjectRelativePath(string $absolutePath): string
    {
        $root = rtrim($this->projectRoot(), '/');

        if (str_starts_with($absolutePath, $root . '/')) {
            return substr($absolutePath, strlen($root) + 1);
        }

        return $absolutePath;
    }

    /**
     * @param array<string, string> $params
     */
    private function fetchScalar(\SQLite3 $db, string $sql, array $params): mixed
    {
        $stmt = $db->prepare($sql);

        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value, SQLITE3_TEXT);
        }

        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_NUM);

        return $row !== false ? $row[0] : null;
    }

    private function variantForStatus(string $status): string
    {
        return match ($status) {
            'published', 'applied' => 'success',
            'pending' => 'warning',
            'failed', 'quarantined' => 'error',
            default => 'active',
        };
    }
}
