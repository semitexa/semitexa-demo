<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\InstallationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: InstallationPayload::class, resource: DemoFeatureResource::class)]
final class InstallationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(InstallationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'Installation is not only about getting files on disk. In Semitexa it should end with a running containerized runtime, a clean operator shell, and enough introspection to trust the project shape immediately.',
            'how' => 'The installer scaffolds the project, the `.env` file becomes the local operating contract, and `bin/semitexa` drives Docker, runtime checks, route discovery, and project introspection from one entry point.',
            'why' => 'If the first boot path is ambiguous, every later problem becomes harder to diagnose. The goal here is to standardize the first hour so the rest of the framework feels mechanical instead of improvised.',
            'keywords' => [
                ['term' => 'install.sh', 'definition' => 'The canonical one-line installer for creating a Semitexa project scaffold.'],
                ['term' => 'bin/semitexa', 'definition' => 'The operator shell for starting, stopping, inspecting, and validating the local runtime.'],
                ['term' => 'self-test', 'definition' => 'A quick runtime health check for local setup validation.'],
                ['term' => 'routes:list', 'definition' => 'CLI command that prints discovered routes so the project shape is inspectable immediately after boot.'],
            ],
        ];

        return $resource
            ->pageTitle('Installation — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'installation',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Get Started')
            ->withSlug('installation')
            ->withTitle('Installation')
            ->withSummary('Create the project, prepare `.env`, and bring up the Semitexa runtime the supported way.')
            ->withEntryLine('The first useful Semitexa experience should end with a running app and an operator shell you can trust, not with a half-finished checklist.')
            ->withHighlights(['install.sh', 'bin/semitexa server:start', 'self-test', 'routes:list'])
            ->withLearnMoreLabel('See the installation flow →')
            ->withDeepDiveLabel('What to verify after boot →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Canonical Install',
                'title' => 'Create the project and get to a trustworthy first boot',
                'summary' => 'Use the installer, set up `.env`, boot the runtime with the Semitexa CLI, and verify the project shape before you start authoring modules.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Run the installer',
                        'summary' => 'The supported install path is the one-line installer. Use the named-directory form only when you want a specific folder immediately.',
                        'commands' => [
                            'curl -fsSL https://semitexa.com/install.sh | bash',
                            'curl -fsSL https://semitexa.com/install.sh | bash -s my-project',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Prepare `.env`',
                        'summary' => 'Copy the example environment before the first boot so ports and local settings stay explicit from the start.',
                        'commands' => [
                            'cd my-project',
                            'cp .env.example .env',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Boot and verify',
                        'summary' => 'Use the operator shell to start, check, and inspect the runtime instead of guessing from container state alone.',
                        'commands' => [
                            'bin/semitexa server:start',
                            'bin/semitexa self-test',
                            'bin/semitexa routes:list --json',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Default Runtime',
                    'rules' => [
                        'Semitexa uses Docker as the supported local runtime boundary.',
                        'The default app URL is http://localhost:9502 unless `SWOOLE_PORT` changes in `.env`.',
                        'You do not need host PHP or Composer for the normal local flow.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Right After Install',
                'title' => 'Do these checks before you touch business code',
                'summary' => 'A clean first boot should be operationally legible. Use the CLI to confirm health, discovery, and current bindings before you start extending the project.',
                'rules' => [
                    'Run `bin/semitexa self-test` when startup feels suspicious instead of debugging blind.',
                    'Use `bin/semitexa logs:errors` as the first log command when the runtime did not boot cleanly.',
                    'Inspect `bin/semitexa describe:project --json` and `bin/semitexa contracts:list --json` early so you know what the project discovered.',
                    'If ORM-backed modules are active, treat `bin/semitexa orm:sync --dry-run` as part of the normal first setup path.',
                ],
            ])
            ->withExplanation($explanation);
    }
}
