<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Core\Discovery\ClassDiscovery;
use Semitexa\Demo\Attributes\DemoFeature;

/**
 * Discovers and holds all demo features.
 *
 * Features are discovered via ClassDiscovery by looking for classes
 * with the #[DemoFeature] attribute. Results are cached after first
 * initialization and sorted by order within each section.
 */
#[AsService]
final class DemoFeatureRegistry
{
    /** @var array<string, list<array{class: string, attribute: DemoFeature}>> keyed by section */
    private array $bySection = [];

    /** @var array<string, array<string, array{class: string, attribute: DemoFeature}>> keyed by section then slug */
    private array $bySectionAndSlug = [];

    private bool $initialized = false;

    /**
     * @return list<array{class: string, attribute: DemoFeature}>
     */
    public function getAll(): array
    {
        $this->initialize();

        $all = [];
        foreach ($this->bySection as $features) {
            foreach ($features as $feature) {
                $all[] = $feature;
            }
        }

        return $all;
    }

    /**
     * @return list<array{class: string, attribute: DemoFeature}>
     */
    public function getBySection(string $section): array
    {
        $this->initialize();

        return $this->bySection[$section] ?? [];
    }

    public function getBySlug(string $section, string $slug): ?DemoFeature
    {
        $this->initialize();

        return $this->bySectionAndSlug[$section][$slug]['attribute'] ?? null;
    }

    /**
     * @return list<string> Section identifiers in discovery order.
     */
    public function getSections(): array
    {
        $this->initialize();

        return array_keys($this->bySection);
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $classes = ClassDiscovery::findClassesWithAttribute(DemoFeature::class);

        foreach ($classes as $className) {
            $reflection = new \ReflectionClass($className);
            $attrs = $reflection->getAttributes(DemoFeature::class);

            if ($attrs === []) {
                continue;
            }

            /** @var DemoFeature $attr */
            $attr = $attrs[0]->newInstance();
            $entry = ['class' => $className, 'attribute' => $attr];

            $this->bySection[$attr->section][] = $entry;
            $this->bySectionAndSlug[$attr->section][$attr->slug] = $entry;
        }

        // Sort each section by order
        foreach ($this->bySection as &$features) {
            usort($features, static fn (array $a, array $b): int => $a['attribute']->order <=> $b['attribute']->order);
        }

        $this->initialized = true;
    }
}
