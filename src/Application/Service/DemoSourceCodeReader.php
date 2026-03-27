<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Core\Util\ProjectRoot;

/**
 * Reads PHP source files and extracts attribute metadata for display in the demo UI.
 *
 * Uses ReflectionClass to locate files — source shown is always the running code.
 */
#[AsService]
final class DemoSourceCodeReader
{
    public function readClassSource(string $className): string
    {
        if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);
        $fileName = $ref->getFileName();

        if ($fileName === false || !is_readable($fileName)) {
            return '';
        }

        $contents = file_get_contents($fileName);

        return $contents !== false ? $this->sanitizeForDisplay($contents) : '';
    }

    /**
     * @return list<\ReflectionAttribute<object>>
     */
    public function extractAttributes(string $className): array
    {
        if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
            return [];
        }

        $ref = new \ReflectionClass($className);

        return $ref->getAttributes();
    }

    public function readProjectRelativeSource(string $relativePath): string
    {
        $path = ProjectRoot::get() . '/' . ltrim($relativePath, '/');

        if (!is_readable($path)) {
            return '';
        }

        $contents = file_get_contents($path);

        return $contents !== false ? $this->sanitizeForDisplay($contents) : '';
    }

    private function sanitizeForDisplay(string $contents): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];
        $filtered = [];
        $skippingDemoFeature = false;

        foreach ($lines as $line) {
            if (preg_match('/^use\s+Semitexa\\\\Demo\\\\Attributes\\\\DemoFeature;$/', trim($line)) === 1) {
                continue;
            }

            if (str_contains($line, '#[DemoFeature(')) {
                $skippingDemoFeature = true;
                continue;
            }

            if ($skippingDemoFeature) {
                if (trim($line) === ')]') {
                    $skippingDemoFeature = false;
                }

                continue;
            }

            $filtered[] = $line;
        }

        $sanitized = implode("\n", $filtered);

        return preg_replace("/\n{3,}/", "\n\n", $sanitized) ?? $sanitized;
    }
}
