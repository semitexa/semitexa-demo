<?php

declare(strict_types=1);

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'dashboard',
    slot: 'sidebar',
    template: '@project/dashboard/sidebar.html.twig',
)]
final class DashboardSidebarSlot extends HtmlSlotResponse
{
    public function withNavigation(array $navigation): static
    {
        return $this->with('navigation', $navigation);
    }

    public function withUserCard(array $userCard): static
    {
        return $this->with('userCard', $userCard);
    }

    public function withMetrics(array $metrics): static
    {
        return $this->with('metrics', $metrics);
    }
}
