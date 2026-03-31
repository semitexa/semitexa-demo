<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\DomainListener;

use Semitexa\Core\Attributes\AsEventListener;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Event\EventExecution;
use Semitexa\Core\Log\LoggerInterface;
use Semitexa\Demo\Application\Payload\Event\DemoDisclosureExpanded;

#[AsEventListener(event: DemoDisclosureExpanded::class, execution: EventExecution::Sync)]
final class DemoDisclosureExpandedListener
{
    #[InjectAsReadonly]
    protected LoggerInterface $logger;

    public function handle(DemoDisclosureExpanded $event): void
    {
        if (!$this->shouldLog()) {
            return;
        }

        $this->logger->debug('Demo disclosure expanded', [
            'component_bridge' => 'disclosure_expanded',
            'target_id' => $event->getTargetId(),
            'source' => $event->getSource(),
            'element_tag' => $event->getElementTag(),
        ]);
    }

    private function shouldLog(): bool
    {
        if (!filter_var($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL)) {
            return false;
        }

        return random_int(1, 20) === 1;
    }
}
