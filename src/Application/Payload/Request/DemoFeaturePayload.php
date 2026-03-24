<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/{section}/{feature}',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    requirements: [
        'section' => '[a-z][a-z0-9-]*',
        'feature' => '[a-z][a-z0-9-]*',
    ],
)]
class DemoFeaturePayload
{
    protected string $section = '';
    protected string $feature = '';

    public function getSection(): string
    {
        return $this->section;
    }

    public function setSection(string $section): void
    {
        $this->section = $section;
    }

    public function getFeature(): string
    {
        return $this->feature;
    }

    public function setFeature(string $feature): void
    {
        $this->feature = $feature;
    }
}
