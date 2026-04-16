<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Docs\Application\Document\RenderedDocument;
use Semitexa\Docs\Application\Document\ResolvedDocument;

final readonly class DemoFeatureDocument
{
    public function __construct(
        public ResolvedDocument $resolved,
        public RenderedDocument $rendered,
    ) {}
}
