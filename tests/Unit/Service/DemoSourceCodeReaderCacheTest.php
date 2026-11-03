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

    protected function setUp(): void
    {
        $this->sources = new \ReflectionProperty(DemoSourceCodeReader::class, 'sources');
        $this->sources->setValue(null, []);
    }

    protected function tearDown(): void
    {
        $this->sources->setValue(null, []);
    }

    #[Test]
    public function a_class_source_is_built_once_per_worker(): void
    {
        $reader = new DemoSourceCodeReader();
        $first = $reader->readClassSource(DemoAnalyticsAggregator::class);
        self::assertNotSame('', $first);

        // What the worker kept is what a later request gets, even from another instance.
        $this->sources->setValue(null, [DemoAnalyticsAggregator::class => 'kept']);

        self::assertSame('kept', (new DemoSourceCodeReader())->readClassSource(DemoAnalyticsAggregator::class));
    }
}
