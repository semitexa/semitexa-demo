<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Demo\Application\Payload\Event\DemoDisclosureExpanded;
use Semitexa\PlatformUi\Application\Service\Primitive\Builtin\ButtonPrimitive;
use Semitexa\PlatformUi\Attribute\UiOn;
use Semitexa\PlatformUi\Attribute\UiPart;
use Semitexa\PlatformUi\Domain\Model\Event\UiInteractionEvent;
use Semitexa\PlatformUi\Domain\Model\Event\UiInteractionResult;
use Semitexa\Ssr\Attribute\AsComponent;

/**
 * A disclosure prompt whose click the server hears: the click reaches this
 * component's own #[UiOn] method through HUG, and the method answers by
 * dispatching a domain event (DemoDisclosureExpanded) to the application's
 * ordinary #[AsEventListener]s. What the prompt points at comes from its own
 * signed props, never from the browser.
 */
#[AsComponent(
    name: 'demo-disclosure-prompt',
    template: '@project-layouts-semitexa-demo/components/disclosure-prompt.html.twig',
    cacheable: false,
)]
#[UiPart(name: 'trigger', uses: ButtonPrimitive::class)]
final class DisclosurePromptComponent
{
    #[UiOn(part: 'trigger', event: 'click')]
    public function onExpand(UiInteractionEvent $event): UiInteractionResult
    {
        $props = $event->props();

        return UiInteractionResult::ack()->dispatching(new DemoDisclosureExpanded(
            targetId: (string) ($props['target'] ?? ''),
            source: (string) ($props['variant'] ?? ''),
            elementTag: 'button',
        ));
    }
}
