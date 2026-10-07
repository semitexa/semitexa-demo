<?php

declare(strict_types=1);

use App\Events\DemoDisclosureExpanded;
use Semitexa\PlatformUi\Application\Service\Primitive\Builtin\ButtonPrimitive;
use Semitexa\PlatformUi\Attribute\UiOn;
use Semitexa\PlatformUi\Attribute\UiPart;
use Semitexa\PlatformUi\Domain\Model\Event\UiInteractionEvent;
use Semitexa\PlatformUi\Domain\Model\Event\UiInteractionResult;
use Semitexa\Ssr\Attribute\AsComponent;

#[AsComponent(name: 'disclosure-prompt', template: '@shop/components/disclosure-prompt.html.twig')]
#[UiPart(name: 'trigger', uses: ButtonPrimitive::class)]
final class DisclosurePromptComponent
{
    #[UiOn(part: 'trigger', event: 'click')]
    public function onExpand(UiInteractionEvent $event): UiInteractionResult
    {
        // Props come from the component's signed context, never from the browser.
        return UiInteractionResult::ack()->dispatching(
            new DemoDisclosureExpanded($event->props()['target'] ?? '', 'product-page', 'button'),
        );
    }
}

/*
disclosure-prompt.html.twig

{% set _ui = ui_component_instance() %}
<span data-ui-component-instance-id="{{ _ui }}" style="display:contents">
  <button type="button" data-ui-part="trigger" data-disclosure-trigger="{{ target }}">{{ label }}</button>
  {{ ui_event_manifest(_ui) }}
</span>
*/

// Still server-rendered UI; the click reaches the component's own #[UiOn] method through HUG.
