<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Docs\Domain\Model\RenderedDocument;
use Semitexa\Docs\Domain\Model\ResolvedDocument;

final readonly class DemoFeatureDocument
{
    public function __construct(
        public ResolvedDocument $resolved,
        public RenderedDocument $rendered,
    ) {}
}
