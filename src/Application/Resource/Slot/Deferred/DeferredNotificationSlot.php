<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Deferred;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_deferred_blocks',
    slot: 'deferred_notification',
    template: '@project-layouts-semitexa-demo/deferred/notification-bell.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/notification-bell.skeleton.html.twig',
    mode: 'template',
    clientModules: ['@project-static-semitexa-demo/deferred/notification-bell.js'],
)]
final class DeferredNotificationSlot extends HtmlSlotResponse
{
    public function withCount(int $count): static
    {
        return $this->with('count', $count);
    }

    public function withNotifications(array $notifications): static
    {
        return $this->with('notifications', $notifications);
    }
}
