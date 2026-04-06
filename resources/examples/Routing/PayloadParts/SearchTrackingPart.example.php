<?php

declare(strict_types=1);

namespace App\Insights\Application\Payload\Part;

use App\Catalog\Application\Payload\Request\SearchPayload;
use Semitexa\Core\Attribute\AsPayloadPart;
use Semitexa\Core\Exception\ValidationException;

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

        if ($campaign !== null && strlen($campaign) > 64) {
            throw new ValidationException(['campaign' => ['Campaign code must stay below 64 characters.']]);
        }

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
