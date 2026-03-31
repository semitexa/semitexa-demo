<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\DemoHomePayload;
use Semitexa\Demo\Application\Resource\Response\DemoHomeResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: DemoHomePayload::class, resource: DemoHomeResource::class)]
final class DemoHomeHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DemoHomePayload $payload, DemoHomeResource $resource): DemoHomeResource
    {
        $sections = $this->catalog->getSections();
        $starterSections = $this->catalog->getStarterSections();
        $featuredFeatures = $this->catalog->getFeaturedFeatures();
        $getStartedGuide = [
            'eyebrow' => 'Get Started',
            'title' => 'Install Semitexa the canonical way and get to a running app fast.',
            'summary' => 'The supported local install flow is the one-line installer. It scaffolds a project, keeps Docker as the runtime boundary, and gets you to a working app without requiring PHP or Composer on the host.',
            'requirements' => [
                [
                    'title' => 'Docker with Compose v2',
                    'detail' => 'Run `docker info` and `docker compose version` before installing. Semitexa uses Docker as the standard local runtime path.',
                ],
                [
                    'title' => 'No host PHP required',
                    'detail' => 'The installer and generated project bootstrap dependencies in containers, so the normal local flow does not depend on a host PHP setup.',
                ],
                [
                    'title' => 'Default local port 9502',
                    'detail' => 'After startup the app is available on `http://localhost:9502`. Change `SWOOLE_PORT` in `.env` if that port is already taken.',
                ],
            ],
            'steps' => [
                [
                    'eyebrow' => 'Step 1',
                    'title' => 'Create a new project',
                    'body' => 'Run the official installer from the parent directory where you want the project folder to be created.',
                    'commands' => [
                        'curl -fsSL https://semitexa.com/install.sh | bash',
                        'curl -fsSL https://semitexa.com/install.sh | bash -s my-project',
                        'curl -fsSL https://semitexa.com/install.sh | bash -s my-project --start',
                    ],
                ],
                [
                    'eyebrow' => 'Step 2',
                    'title' => 'Enter the project and prepare the environment',
                    'body' => 'Move into the generated directory, copy the example environment file, and adjust values only if your local machine needs different ports or service settings.',
                    'commands' => [
                        'cd my-project',
                        'cp .env.example .env',
                    ],
                ],
                [
                    'eyebrow' => 'Step 3',
                    'title' => 'Start the application',
                    'body' => 'Use the Semitexa CLI wrapper. This is the canonical way to boot the local runtime and its Docker services.',
                    'commands' => [
                        'bin/semitexa server:start',
                        'bin/semitexa server:stop',
                    ],
                ],
                [
                    'eyebrow' => 'Step 4',
                    'title' => 'Open the app and keep moving',
                    'body' => 'Once the runtime is up, open the default local URL and move straight into the first real framework tasks instead of staying in setup mode.',
                    'commands' => [
                        'http://localhost:9502',
                        'bin/semitexa list',
                    ],
                ],
            ],
            'rules' => [
                'Treat `curl -fsSL https://semitexa.com/install.sh | bash` as the canonical local install path.',
                'Run Semitexa through Docker, not `php server.php` on the host.',
                'Use `bin/semitexa` as the operator entry point for starting, stopping, and inspecting the app.',
            ],
            'nextReads' => [
                [
                    'label' => 'Build the first page',
                    'href' => 'https://semitexa.com/docs/MINIMAL_PAGE',
                    'detail' => 'Move from install into the first Twig page and see the request-to-response path working end to end.',
                ],
                [
                    'label' => 'Understand runtime controls',
                    'href' => '/demo/cli/runtime-maintenance',
                    'detail' => 'See how cache clear, reload, and registry maintenance look from the framework shell.',
                ],
                [
                    'label' => 'Inspect the live feature map',
                    'href' => '#sections',
                    'detail' => 'Browse the working `/demo/...` routes and move from setup into real framework surfaces.',
                ],
            ],
        ];
        $homeCatalog = [
            'sections' => $sections,
            'starterSections' => $starterSections,
            'featuredFeatures' => $featuredFeatures,
            'totalFeatureCount' => $this->catalog->getTotalFeatureCount(),
            'getStartedGuide' => $getStartedGuide,
        ];

        return $resource
            ->pageTitle('Semitexa Demo — Build faster. Ship safer. Scale effortlessly.')
            ->withDemoShellContext([
                'navSections' => $sections,
                'featureTree' => $sections,
                'currentSection' => null,
                'currentSlug' => null,
                'infoWhat' => 'Production-like walkthroughs for the Semitexa runtime, not disconnected toy snippets.',
                'infoHow' => 'Start from the shell, open a section, then drill into feature pages with live previews and source.',
                'infoWhy' => 'A demo package should prove that the framework feels coherent before anyone reads the docs.',
                'infoKeywords' => [],
            ])
            ->withRelease([
                'label' => 'First release',
                'date' => '22 April 2026',
                'target' => '2026-04-22T00:00:00+03:00',
            ])
            ->withHomeCatalog($homeCatalog);
    }
}
