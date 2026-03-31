<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\DomainListener;

use Semitexa\Core\Attributes\AsEventListener;
use Semitexa\Core\Event\EventExecution;
use Semitexa\Demo\Application\Payload\Event\DemoDisclosureExpanded;

#[AsEventListener(event: DemoDisclosureExpanded::class, execution: EventExecution::Sync)]
final class DemoDisclosureExpandedListener
{
    public function handle(DemoDisclosureExpanded $event): void
    {
        error_log(json_encode([
            'component_bridge' => 'disclosure_expanded',
            'target_id' => $event->getTargetId(),
            'source' => $event->getSource(),
            'element_tag' => $event->getElementTag(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
