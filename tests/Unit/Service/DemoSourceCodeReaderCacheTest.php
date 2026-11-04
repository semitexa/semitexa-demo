<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Semitexa\Demo\Application\Service\DemoAnalyticsAggregator;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

/**
 * Every demo page read and rendered up to three class sources on every
 * request. A worker keeps each one: it never sees a class change without a
 * restart.
 */
final class DemoSourceCodeReaderCacheTest extends TestCase
{
    private \ReflectionProperty $sources;

    /** @var array<string, string> what the process held before this test */
    private array $saved = [];

    private \ReflectionProperty $projectSources;

    /** @var array<string, string> */
    private array $savedProjectSources = [];

    protected function setUp(): void
    {
        $this->sources = new \ReflectionProperty(DemoSourceCodeReader::class, 'sources');
        /** @var array<string, string> $saved */
        $saved = $this->sources->getValue();
        $this->saved = $saved;
        $this->sources->setValue(null, []);
        $this->projectSources = new \ReflectionProperty(DemoSourceCodeReader::class, 'projectSources');
        /** @var array<string, string> $savedProjects */
        $savedProjects = $this->projectSources->getValue();
        $this->savedProjectSources = $savedProjects;
        $this->projectSources->setValue(null, []);
    }

    protected function tearDown(): void
    {
        $this->sources->setValue(null, $this->saved);
        $this->projectSources->setValue(null, $this->savedProjectSources);
    }

    #[Test]
    public function a_class_source_is_built_once_per_worker(): void
    {
        $reader = new DemoSourceCodeReader();
        $first = $reader->readClassSource(DemoAnalyticsAggregator::class);
        self::assertNotSame('', $first);
        self::assertSame([DemoAnalyticsAggregator::class => $first], $this->sources->getValue(), 'the first read is kept');

        // What the worker kept is what a later request gets, even from another instance.
        $this->sources->setValue(null, [DemoAnalyticsAggregator::class => 'kept']);

        self::assertSame('kept', (new DemoSourceCodeReader())->readClassSource(DemoAnalyticsAggregator::class));
    }

    #[Test]
    public function an_example_file_is_read_once_per_worker(): void
    {
        $path = 'packages/semitexa-demo/resources/examples/Data/Relations/ProductResource.example.php';
        $first = (new DemoSourceCodeReader())->readProjectRelativeSource($path);
        self::assertNotSame('', $first);
        self::assertSame([$path => $first], $this->projectSources->getValue(), 'the first read is kept');

        $this->projectSources->setValue(null, [$path => 'kept']);

        self::assertSame('kept', (new DemoSourceCodeReader())->readProjectRelativeSource($path));
    }

    #[Test]
    public function a_failed_read_is_not_kept(): void
    {
        $path = 'packages/semitexa-demo/resources/examples/Missing/Nothing.example.php';

        self::assertSame('', (new DemoSourceCodeReader())->readProjectRelativeSource($path));
        self::assertSame([], $this->projectSources->getValue(), 'read again next time, in case it becomes readable');
    }
}
