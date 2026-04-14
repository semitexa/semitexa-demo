<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Console\Command;

use Semitexa\Core\Attribute\AsCommand;
use Semitexa\Ssr\Async\AsyncResourceSseServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'demo:deferred-heartbeat', description: 'Broadcast a deferred heartbeat to all authenticated kiss sessions')]
final class DemoDeferredHeartbeatCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('demo:deferred-heartbeat')
            ->setDescription('Broadcast a deferred heartbeat to all authenticated kiss sessions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sentAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $minute = (int) $sentAt->format('i');
        $second = (int) $sentAt->format('s');
        $nextExpectedAt = $sentAt->modify('+60 seconds');
        $chartValues = [
            42 + (($minute + 1) % 7) * 3,
            61 + (($minute + 3) % 5) * 4,
            48 + (($minute + 5) % 6) * 5,
            70 + (($minute + 2) % 4) * 6,
            55 + (($minute + 4) % 5) * 5,
        ];
        $reviewBodies = [
            'Scheduler confirmed another backend publish without opening a second SSE connection.',
            'Shared kiss stayed open and delivered the heartbeat to the page in place.',
            'Deferred regions finished earlier; live runtime traffic kept flowing through the same channel.',
            'The browser received a backend heartbeat without falling back to extra fetch recovery.',
        ];
        $reviewIndex = $minute % count($reviewBodies);

        $delivered = AsyncResourceSseServer::deliverToAuthenticatedUsers([
            'id' => 'demo_deferred_heartbeat_' . $sentAt->format('YmdHis'),
            'event' => 'scheduler.tick',
            'level' => 'success',
            'title' => 'Deferred heartbeat',
            'message' => sprintf(
                'Cron heartbeat fired at %s UTC and was broadcast through the shared kiss stream.',
                $sentAt->format('H:i:s')
            ),
            'source' => 'scheduler',
            'sent_at' => $sentAt->format(DATE_ATOM),
            'cadence_seconds' => 60,
            'notification' => [
                'level' => ($minute % 2) === 0 ? 'success' : 'info',
                'message' => sprintf(
                    'Backend heartbeat delivered at %s UTC over the shared kiss stream.',
                    $sentAt->format('H:i:s')
                ),
                'count_delta' => 1,
            ],
            'chart' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'values' => $chartValues,
                'summary' => sprintf(
                    'Backend metrics snapshot refreshed at %s UTC.',
                    $sentAt->format('H:i:s')
                ),
            ],
            'review' => [
                'rating' => 4 + ($minute % 2),
                'body' => $reviewBodies[$reviewIndex],
            ],
            'countdown' => [
                'duration_seconds' => 60,
                'next_expected_at' => $nextExpectedAt->format(DATE_ATOM),
                'summary' => sprintf(
                    'Timer re-synced from the backend heartbeat at %s UTC.',
                    $sentAt->format('H:i:s')
                ),
            ],
        ]);

        $io->success("Delivered heartbeat to {$delivered} authenticated session(s).");

        return Command::SUCCESS;
    }
}
