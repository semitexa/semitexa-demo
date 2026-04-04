<?php

declare(strict_types=1);

namespace App\Application\Listener;

use App\Application\Event\DemoDisclosureExpanded;
use Semitexa\Core\Attributes\AsEventListener;

#[AsEventListener(event: DemoDisclosureExpanded::class)]
final class DemoDisclosureExpandedListener
{
    public function handle(DemoDisclosureExpanded $event): void
    {
        // Persist analytics or emit follow-up UI signals.
    }
}
