<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Support\ProjectRoot;
use Semitexa\Docs\Application\Service\DocumentHtmlRenderer;
use Semitexa\Docs\Domain\Model\DocumentId;
use Semitexa\Docs\Domain\Model\DocumentMetadata;
use Semitexa\Docs\Domain\Model\ResolvedDocument;
use Semitexa\Update\Application\Service\Changelog\PackageChangelogReader;
use Semitexa\Update\Domain\Model\Changelog\ReleaseNote;

#[AsService]
final class DemoChangelogProvider
{
    #[InjectAsReadonly]
    protected DocumentHtmlRenderer $renderer;

    /**
     * @return array{
     *   releases: list<array{package: string, version: string, date: ?string, isUnreleased: bool, html: string}>,
     *   releaseCount: int,
     *   packageCount: int
     * }
     */
    public function page(): array
    {
        $projectRoot = ProjectRoot::get();
        $reader = new PackageChangelogReader($projectRoot);
        $releases = [];

        foreach ($this->discoverPackages($projectRoot) as $package) {
            foreach ($reader->allNotes($package) as $note) {
                $releases[] = $this->present($reader, $note);
            }
        }

        usort($releases, $this->newestFirst(...));

        return [
            'releases' => $releases,
            'releaseCount' => count($releases),
            'packageCount' => count(array_unique(array_column($releases, 'package'))),
        ];
    }

    /**
     * @return list<string>
     */
    private function discoverPackages(string $projectRoot): array
    {
        $packages = [];
        $patterns = [
            $projectRoot . '/vendor/semitexa/*/CHANGELOG.md',
            $projectRoot . '/packages/semitexa-*/CHANGELOG.md',
        ];

        foreach ($patterns as $pattern) {
            foreach (glob($pattern) ?: [] as $path) {
                $directory = basename(dirname($path));
                $shortName = str_starts_with($directory, 'semitexa-')
                    ? substr($directory, strlen('semitexa-'))
                    : $directory;

                if ($shortName !== '') {
                    $packages['semitexa/' . $shortName] = true;
                }
            }
        }

        $names = array_keys($packages);
        sort($names, SORT_STRING);

        return $names;
    }

    /**
     * @return array{package: string, version: string, date: ?string, isUnreleased: bool, html: string}
     */
    private function present(PackageChangelogReader $reader, ReleaseNote $note): array
    {
        $document = new ResolvedDocument(
            id: new DocumentId('changelog', hash('sha256', $note->package . '@' . $note->version)),
            metadata: new DocumentMetadata(
                title: $note->package . ' ' . $note->version,
                summary: 'Release notes for ' . $note->package . ' ' . $note->version . '.',
                order: 0,
            ),
            markdown: $note->body,
            path: $reader->changelogPath($note->package) ?? '',
        );

        return [
            'package' => $note->package,
            'version' => $note->version,
            'date' => $note->date,
            'isUnreleased' => strcasecmp($note->version, 'Unreleased') === 0,
            'html' => $this->renderer->renderHtml($document)->content,
        ];
    }

    /**
     * @param array{package: string, version: string, date: ?string, isUnreleased: bool, html: string} $left
     * @param array{package: string, version: string, date: ?string, isUnreleased: bool, html: string} $right
     */
    private function newestFirst(array $left, array $right): int
    {
        if ($left['isUnreleased'] !== $right['isUnreleased']) {
            return $left['isUnreleased'] ? -1 : 1;
        }

        $leftOrder = $left['date'] ?? $left['version'];
        $rightOrder = $right['date'] ?? $right['version'];
        $byRelease = strnatcasecmp($rightOrder, $leftOrder);

        return $byRelease !== 0 ? $byRelease : strcmp($left['package'], $right['package']);
    }
}
