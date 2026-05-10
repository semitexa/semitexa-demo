<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoSectionResource;

#[AsPublicPayload(
    path: '/demo/{section}',
    methods: ['GET'],
    responseWith: DemoSectionResource::class,
    produces: ['application/json', 'text/html'],
    requirements: [
        'section' => '[a-z][a-z0-9-]+',
    ],
)]
class DemoSectionPayload
{
    protected string $section = '';

    public function getSection(): string
    {
        return $this->section;
    }

    public function setSection(string $section): void
    {
        $this->section = $section;
    }
}
