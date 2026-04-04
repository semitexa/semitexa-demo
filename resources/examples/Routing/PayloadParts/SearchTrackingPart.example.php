<?php

declare(strict_types=1);

namespace App\Insights\Application\Payload\Part;

use App\Catalog\Application\Payload\Request\SearchPayload;
use Semitexa\Core\Attribute\AsPayloadPart;

#[AsPayloadPart(base: SearchPayload::class)]
trait SearchTrackingPart
{
    protected ?string $campaign = null;
    protected bool $preview = false;

    public function getCampaign(): ?string
    {
        return $this->campaign;
    }

    public function setCampaign(?string $campaign): void
    {
        $campaign = $campaign !== null ? trim($campaign) : null;
        $this->campaign = $campaign !== '' ? $campaign : null;
    }

    public function isPreview(): bool
    {
        return $this->preview;
    }

    public function setPreview(bool $preview): void
    {
        $this->preview = $preview;
    }
}
