<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Composer\InstalledVersions;
use Semitexa\Core\Attribute\AsService;

#[AsService]
final class UltimateVersionProvider
{
    private const PACKAGE = 'semitexa/ultimate';

    private ?string $version = null;

    private bool $resolved = false;

    public function current(): ?string
    {
        if ($this->resolved) {
            return $this->version;
        }

        $this->resolved = true;
        $this->version = $this->releaseVersion((string) (getenv('SEMITEXA_RELEASE_VERSION') ?: ''))
            ?? $this->installedVersion()
            ?? $this->rootPackageVersion()
            ?? $this->manifestVersion(dirname(__DIR__, 4) . '/semitexa-ultimate/composer.json');

        return $this->version;
    }

    private function installedVersion(): ?string
    {
        if (!class_exists(InstalledVersions::class) || !InstalledVersions::isInstalled(self::PACKAGE)) {
            return null;
        }

        return $this->releaseVersion((string) (InstalledVersions::getPrettyVersion(self::PACKAGE) ?? ''));
    }

    private function rootPackageVersion(): ?string
    {
        if (!class_exists(InstalledVersions::class)) {
            return null;
        }

        $root = InstalledVersions::getRootPackage();
        if (($root['name'] ?? null) !== self::PACKAGE) {
            return null;
        }

        $version = $this->releaseVersion((string) ($root['pretty_version'] ?? ''));
        if ($version !== null) {
            return $version;
        }

        $installPath = $root['install_path'] ?? null;

        return is_string($installPath)
            ? $this->manifestVersion($installPath . '/composer.json')
            : null;
    }

    private function manifestVersion(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $manifest = json_decode((string) file_get_contents($path), true);
        if (!is_array($manifest)) {
            return null;
        }

        $declaredVersion = $this->releaseVersion((string) ($manifest['version'] ?? ''));
        if ($declaredVersion !== null) {
            return $declaredVersion;
        }

        $requirements = $manifest['require'] ?? null;

        return is_array($requirements)
            ? $this->releaseVersion((string) ($requirements['semitexa/core'] ?? ''))
            : null;
    }

    private function releaseVersion(string $candidate): ?string
    {
        $candidate = ltrim(trim($candidate), 'v');

        return preg_match('/^\d{4}\.\d{2}\.\d{2}\.\d{4}(?:-(?:alpha|beta|rc)\.\d+)?$/', $candidate) === 1
            ? $candidate
            : null;
    }
}
