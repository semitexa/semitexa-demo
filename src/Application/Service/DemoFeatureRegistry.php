<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
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
    #[InjectAsReadonly]
    protected ClassDiscovery $classDiscovery;

    /** @var array<string, list<array{class: string, attribute: DemoFeature, path: ?string}>> keyed by section */
    private array $bySection = [];

    /** @var array<string, array<string, array{class: string, attribute: DemoFeature, path: ?string}>> keyed by section then slug */
    private array $bySectionAndSlug = [];

    private bool $initialized = false;

    /**
     * @return list<array{class: string, attribute: DemoFeature, path: ?string}>
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
     * @return list<array{class: string, attribute: DemoFeature, path: ?string}>
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

    public function getPath(string $section, string $slug): ?string
    {
        $this->initialize();

        return $this->bySectionAndSlug[$section][$slug]['path'] ?? null;
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

        $classes = $this->classDiscovery->findClassesWithAttribute(DemoFeature::class);

        foreach ($classes as $className) {
            $reflection = new \ReflectionClass($className);
            $attrs = $reflection->getAttributes(DemoFeature::class);

            if ($attrs === []) {
                continue;
            }

            /** @var DemoFeature $attr */
            $attr = $attrs[0]->newInstance();
            $payloadAttrs = $reflection->getAttributes(AsPayload::class);
            $payload = $payloadAttrs === [] ? null : $payloadAttrs[0]->newInstance();
            $entry = [
                'class' => $className,
                'attribute' => $attr,
                'path' => $payload instanceof AsPayload ? $this->resolvePath($payload) : null,
            ];

            $this->bySection[$attr->section][] = $entry;
            $this->bySectionAndSlug[$attr->section][$attr->slug] = $entry;
        }

        // Sort each section by order
        foreach ($this->bySection as &$features) {
            usort($features, static fn (array $a, array $b): int => $a['attribute']->order <=> $b['attribute']->order);
        }

        $this->initialized = true;
    }

    private function resolvePath(AsPayload $payload): ?string
    {
        $path = $this->resolveEnvPath($payload->path);
        if ($path === null || $path === '') {
            return null;
        }

        if ($payload->defaults === null || $payload->defaults === []) {
            return $path;
        }

        return preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)\}/',
            static function (array $matches) use ($payload): string {
                $key = $matches[1];
                $value = $payload->defaults[$key] ?? null;

                return is_scalar($value) ? rawurlencode((string) $value) : $matches[0];
            },
            $path,
        ) ?? $path;
    }

    private function resolveEnvPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }

        if (!str_starts_with($path, 'env::')) {
            return $path;
        }

        $parts = explode('::', $path, 3);
        if (count($parts) !== 3 || $parts[1] === '') {
            return $path;
        }

        $value = getenv($parts[1]);
        if (!is_string($value) || $value === '') {
            return $parts[2];
        }

        return $value;
    }
}
