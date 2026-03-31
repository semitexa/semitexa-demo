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
        $relativePath = ltrim($relativePath, '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return '';
        }

        foreach ($this->resolveReadableCandidates($relativePath) as $path) {
            $contents = file_get_contents($path);
            if ($contents !== false) {
                return $this->sanitizeForDisplay($contents);
            }
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private function resolveReadableCandidates(string $relativePath): array
    {
        $candidates = [];
        $strippedDemoPath = null;

        if (str_starts_with($relativePath, 'packages/semitexa-demo/')) {
            $strippedDemoPath = substr($relativePath, strlen('packages/semitexa-demo/'));
        }

        foreach ($this->candidateRoots() as $root) {
            $root = rtrim($root, '/');
            $candidates[] = $this->resolveCandidateWithinRoot($root, $relativePath);

            if ($strippedDemoPath !== null && str_ends_with($root, '/packages/semitexa-demo')) {
                $candidates[] = $this->resolveCandidateWithinRoot(
                    $root,
                    $strippedDemoPath,
                );
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * @return list<string>
     */
    private function candidateRoots(): array
    {
        $packageRoot = dirname(__DIR__, 3);
        $monorepoRoot = dirname($packageRoot, 2);

        return array_values(array_unique([
            rtrim(ProjectRoot::get(), '/'),
            rtrim($packageRoot, '/'),
            rtrim($monorepoRoot, '/'),
        ]));
    }

    private function resolveCandidateWithinRoot(string $root, string $relativePath): ?string
    {
        $path = realpath($root . '/' . $relativePath);
        if ($path === false || !is_file($path) || !str_starts_with($path, $root . '/') || !is_readable($path)) {
            return null;
        }

        return $path;
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
