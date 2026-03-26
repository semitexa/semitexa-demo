<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attributes\AsResource;
use Semitexa\Core\Contract\ResourceInterface;

#[AsResource(
    handle: 'demo_deferred_blocks',
    template: '@project-layouts-semitexa-demo/pages/deferred-blocks.html.twig',
)]
class DeferredBlocksDemoResource extends DemoFeatureResource implements ResourceInterface
{
}
