<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Testing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Testing\ScaffoldingGeneratorsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Dev\Console\Command\MakeContractCommand;
use Semitexa\Dev\Console\Command\MakeModuleCommand;
use Semitexa\Dev\Console\Command\MakePageCommand;
use Semitexa\Dev\Console\Command\MakePayloadCommand;
use Semitexa\Dev\Console\Command\MakeServiceCommand;

#[AsPayloadHandler(payload: ScaffoldingGeneratorsPayload::class, resource: DemoFeatureResource::class)]
final class ScaffoldingGeneratorsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ScaffoldingGeneratorsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('cli', 'scaffolding-generators') ?? [];

        return $resource
            ->pageTitle('Scaffolding Generators — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'cli',
                'currentSlug' => 'scaffolding-generators',
                'infoWhat' => $explanation['what'] ?? 'Semitexa scaffolding commands encode framework conventions directly into generated files and hints.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('cli')
            ->withSlug('scaffolding-generators')
            ->withTitle('Scaffolding Generators')
            ->withSummary('Scaffold modules, pages, payloads, services, and contracts through commands that already understand Semitexa structure and AI-friendly output modes.')
            ->withEntryLine('The generator surface matters because it teaches the framework shape by producing the right files, not by asking the developer to remember ceremony.')
            ->withHighlights(['make:module', 'make:page', 'make:payload', 'make:service', 'make:contract', '--llm-hints'])
            ->withLearnMoreLabel('See the generator workflow →')
            ->withDeepDiveLabel('Why this scaffolding is different →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/cli-command-workbench.html.twig', [
                'eyebrow' => 'Code Generation',
                'title' => 'Scaffolding that already knows Semitexa conventions',
                'summary' => 'These commands do not just dump stubs. They encode module structure, naming, response boundaries, and even AI-oriented follow-up hints, so the generated result starts aligned with the framework.',
                'pillars' => [
                    [
                        'title' => 'Structure-aware.',
                        'summary' => 'make:module and make:page know the expected module, payload, handler, resource, and template layout without asking the developer to assemble it manually.',
                    ],
                    [
                        'title' => 'Machine-readable when needed.',
                        'summary' => 'Dry-run, JSON, and --llm-hints modes let both humans and agents inspect the plan before committing files.',
                    ],
                    [
                        'title' => 'Teaches the shape.',
                        'summary' => 'Good scaffolding shortens onboarding because the produced files demonstrate the intended Semitexa architecture directly.',
                    ],
                ],
                'commands' => [
                    [
                        'name' => 'bin/semitexa make:module --name=Catalog',
                        'purpose' => 'Create a new module with standard directories already in place.',
                        'value' => 'Removes the need to remember directory conventions or Composer changes.',
                    ],
                    [
                        'name' => 'bin/semitexa make:page --module=Catalog --name=Pricing --path=/pricing --method=GET',
                        'purpose' => 'Scaffold a full SSR page boundary in one step.',
                        'value' => 'Creates the payload, handler, resource, and template as a coherent unit.',
                    ],
                    [
                        'name' => 'bin/semitexa make:payload --module=Catalog --name=CreateProduct --path=/products --method=POST --response=CreateProduct',
                        'purpose' => 'Generate only the transport boundary when you need a narrower step.',
                        'value' => 'Useful when the payload contract should be reviewed before the rest of the implementation.',
                    ],
                    [
                        'name' => 'bin/semitexa make:contract --module=Catalog --name=PriceFeed --implementation=ApiPriceFeed --llm-hints',
                        'purpose' => 'Scaffold a DI contract plus implementation and emit follow-up hints.',
                        'value' => 'Great fit for AI-assisted workflows because the command can describe what to fill next.',
                    ],
                ],
                'snippets' => [
                    [
                        'label' => 'Preview a new page without writing files',
                        'code' => "bin/semitexa make:page --module=Catalog --name=Pricing --path=/pricing --method=GET --dry-run",
                    ],
                    [
                        'label' => 'Generate a payload with agent-friendly hints',
                        'code' => "bin/semitexa make:payload --module=Catalog --name=CreateProduct --path=/products --method=POST --response=CreateProduct --llm-hints",
                    ],
                    [
                        'label' => 'Scaffold a contract and verify it after generation',
                        'code' => "bin/semitexa make:contract --module=Catalog --name=PriceFeed --implementation=ApiPriceFeed\nbin/semitexa contracts:list --json",
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Generator Rule',
                'title' => 'When scaffolding actually adds value',
                'summary' => 'The point is not fewer keystrokes. The point is fewer incorrect architectural starts.',
                'rules' => [
                    'Use generators to establish the canonical file and attribute shape before implementation details creep in.',
                    'Prefer dry-run, JSON, or llm-hints when you want reviewers or agents to inspect the plan before files are written.',
                    'Treat generated files as architectural starting points, not as final code that excuses design thinking.',
                    'A scaffold is successful when it reduces wrong framework patterns, not only when it saves typing.',
                ],
            ])
            ->withSourceCode([
                'make:module Command' => $this->sourceCodeReader->readClassSource(MakeModuleCommand::class),
                'make:page Command' => $this->sourceCodeReader->readClassSource(MakePageCommand::class),
                'make:payload Command' => $this->sourceCodeReader->readClassSource(MakePayloadCommand::class),
                'make:service Command' => $this->sourceCodeReader->readClassSource(MakeServiceCommand::class),
                'make:contract Command' => $this->sourceCodeReader->readClassSource(MakeContractCommand::class),
            ])
            ->withExplanation($explanation);
    }
}
