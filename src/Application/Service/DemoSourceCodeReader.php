<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

/**
 * Reads PHP source files and extracts attribute metadata for display in the demo UI.
 *
 * Uses ReflectionClass to locate files — source shown is always the running code.
 */
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

        return file_get_contents($fileName);
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
}
