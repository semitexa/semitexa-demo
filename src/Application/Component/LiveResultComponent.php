<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attribute\AsComponent;

#[AsComponent(
    name: 'demo-live-result',
    template: '@project-layouts-semitexa-demo/components/live-result.html.twig',
)]
final class LiveResultComponent
{
    public string $endpoint = '';
    public string $method = 'GET';
    public string $label = 'Send request';
    public string $resultId = 'live-result';
}
