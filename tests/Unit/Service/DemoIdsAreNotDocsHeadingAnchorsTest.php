<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Semitexa\Docs\Application\Service\DocumentHtmlRenderer;

/**
 * A demo page embeds a docs page's rendered HTML, and the docs renderer gives
 * every heading the anchor GitHub gives it, as its id. An id the demo assigns
 * on the same page must not be one of those anchors. Two elements would share
 * the id, and getElementById() would find whichever comes first. The arena
 * preview's root, `execution-arena`, is the anchor of events/arena.md's title.
 */
final class DemoIdsAreNotDocsHeadingAnchorsTest extends TestCase
{
    #[Test]
    public function no_id_the_demo_assigns_is_the_anchor_of_a_docs_heading(): void
    {
        $collisions = array_values(array_intersect($this->demoIds(), $this->docsHeadingAnchors()));
        sort($collisions);

        self::assertSame([], $collisions, 'these demo ids are also docs heading anchors; rename the demo id');
    }

    /** @return list<string> */
    private function demoIds(): array
    {
        $root = dirname(__DIR__, 3) . '/src';
        $ids = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || !in_array($file->getExtension(), ['twig', 'js', 'php'], true)) {
                continue;
            }
            $code = (string) file_get_contents($file->getPathname());
            preg_match_all('/\bid="([a-z0-9][a-z0-9_-]*)"|getElementById\([\'"]([a-z0-9][a-z0-9_-]*)[\'"]\)/', $code, $m);
            foreach (array_merge($m[1], $m[2]) as $id) {
                if ($id !== '') {
                    $ids[$id] = true;
                }
            }
        }
        self::assertNotSame([], $ids, 'the demo assigns ids; finding none means the scan is broken');

        return array_keys($ids);
    }

    /**
     * The anchor of every heading in the docs pages, as GitHub writes it:
     * lowercase, punctuation dropped (underscores and hyphens kept), each space
     * a hyphen.
     *
     * @return list<string>
     */
    private function docsHeadingAnchors(): array
    {
        // The docs package as installed beside the demo: found through the autoloader.
        $docs = dirname((string) (new \ReflectionClass(DocumentHtmlRenderer::class))->getFileName(), 4) . '/docs/en';
        self::assertDirectoryExists($docs);
        $anchors = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($docs, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'md') {
                continue;
            }
            $body = (string) preg_replace('/```.*?```/s', '', (string) file_get_contents($file->getPathname()));
            preg_match_all('/^#{1,6}\s+(.+?)\s*#*\s*$/m', $body, $headings);
            foreach ($headings[1] as $heading) {
                $text = (string) preg_replace(['/`([^`]*)`/', '/\[([^\]]*)\]\([^)]*\)/'], ['$1', '$1'], $heading);
                $slug = (string) preg_replace('/[^\p{L}\p{Nd}\p{Nl}\p{M}\s_-]+/u', '', mb_strtolower(trim($text), 'UTF-8'));
                $anchors[(string) preg_replace('/\s/u', '-', $slug)] = true;
            }
        }
        self::assertArrayHasKey('execution-arena', $anchors, 'events/arena.md\'s title is found, so the scan reads the docs');

        return array_keys($anchors);
    }
}
