<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Event;

use Semitexa\Core\Attributes\AsEvent;

#[AsEvent]
final class DemoItemCreated
{
    private string $itemId;
    private string $itemName;
    private string $section;
    private float $timestamp;

    public function getItemId(): string { return $this->itemId; }
    public function setItemId(string $itemId): void { $this->itemId = $itemId; }

    public function getItemName(): string { return $this->itemName; }
    public function setItemName(string $itemName): void { $this->itemName = $itemName; }

    public function getSection(): string { return $this->section; }
    public function setSection(string $section): void { $this->section = $section; }

    public function getTimestamp(): float { return $this->timestamp; }
    public function setTimestamp(float $timestamp): void { $this->timestamp = $timestamp; }
}
