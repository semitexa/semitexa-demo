<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Testing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Testing\PayloadContractsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Testing\Attribute\TestablePayload;
use Semitexa\Testing\Console\Command\TestInitCommand;
use Semitexa\Testing\Console\Command\TestRunCommand;
use Semitexa\Testing\PayloadContractTester;
use Semitexa\Testing\Strategy\MonkeyTestingStrategy;
use Semitexa\Testing\Strategy\Profile\ParanoidProfileStrategy;
use Semitexa\Testing\Strategy\Profile\StrictProfileStrategy;

#[AsPayloadHandler(payload: PayloadContractsPayload::class, resource: DemoFeatureResource::class)]
final class PayloadContractsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(PayloadContractsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'testing',
            slug: 'payload-contracts',
            entryLine: 'Testing in Semitexa can start from the transport boundary itself: payloads declare what should be verified, and the framework runs the strategy suite.',
            learnMoreLabel: 'See the testing workflow →',
            deepDiveLabel: 'What the profiles actually buy you →',
            relatedSlugs: [],
            fallbackTitle: 'Payload Contract Testing',
            fallbackSummary: 'Scaffold one project-level contract test and let strategy profiles verify payload boundaries without hand-writing repetitive negative cases.',
            fallbackHighlights: ['#[TestablePayload]', 'test:init', 'test:run', 'StrictProfileStrategy', 'MonkeyTestingStrategy'],
            explanation: $this->explanationProvider->getExplanation('testing', 'payload-contracts') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                '#[TestablePayload]' => $this->sourceCodeReader->readClassSource(TestablePayload::class),
                'PayloadContractTester' => $this->sourceCodeReader->readClassSource(PayloadContractTester::class),
                'test:init Command' => $this->sourceCodeReader->readClassSource(TestInitCommand::class),
                'test:run Command' => $this->sourceCodeReader->readClassSource(TestRunCommand::class),
                'StrictProfileStrategy' => $this->sourceCodeReader->readClassSource(StrictProfileStrategy::class),
                'ParanoidProfileStrategy' => $this->sourceCodeReader->readClassSource(ParanoidProfileStrategy::class),
                'MonkeyTestingStrategy' => $this->sourceCodeReader->readClassSource(MonkeyTestingStrategy::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/cli-command-workbench.html.twig', [
                'eyebrow' => 'Contract Testing',
                'title' => 'One scaffolded suite can exercise every testable payload',
                'summary' => 'Instead of hand-writing the same validation and bad-input cases for every endpoint, Semitexa lets payloads declare their testing strategies and runs the composed suite through a shared transport.',
                'pillars' => [
                    ['title' => 'Scaffold once.', 'summary' => 'test:init creates the universal ProjectPayloadsContractTest that auto-discovers payloads marked with #[TestablePayload].'],
                    ['title' => 'Choose a profile.', 'summary' => 'Strict and Paranoid profiles expand into concrete strategies like method checks, type mutation, security assertions, and monkey input.'],
                    ['title' => 'Run through real transport.', 'summary' => 'The contract tester sends actual requests and reports which strategy or generated case failed instead of hiding everything in custom helpers.'],
                ],
                'commands' => [
                    ['name' => 'bin/semitexa test:init', 'purpose' => 'Scaffold the universal payload contract test into tests/Payload.', 'value' => 'Gives the project one canonical entrypoint for payload boundary verification.'],
                    ['name' => 'bin/semitexa test:run -- --filter ProjectPayloadsContractTest', 'purpose' => 'Run only the generated contract suite inside the dev Docker stack.', 'value' => 'Keeps feedback focused while expanding coverage across all marked payloads.'],
                    ['name' => 'bin/semitexa test:run -- --testdox', 'purpose' => 'Forward normal PHPUnit flags through the Semitexa wrapper.', 'value' => 'Developers keep PHPUnit ergonomics while preserving the project container setup.'],
                ],
                'snippets' => [
                    [
                        'label' => 'Mark a payload as contract-testable',
                        'code' => <<<'PHP'
#[AsPayload(path: '/api/payments', methods: ['POST'])]
#[TestablePayload(
    strategies: [StrictProfileStrategy::class, ParanoidProfileStrategy::class],
    context: ['fail_fast' => false],
)]
final class PaymentPayload {}
PHP,
                    ],
                    ['label' => 'Scaffold and run the suite', 'code' => "bin/semitexa test:init\nbin/semitexa test:run -- --filter ProjectPayloadsContractTest"],
                    [
                        'label' => 'What Paranoid profile expands to',
                        'code' => <<<'PHP'
ParanoidProfileStrategy::class => [
    SecurityStrategy::class,
    HttpMethodStrategy::class,
    TypeEnforcementStrategy::class,
    MonkeyTestingStrategy::class,
]
PHP,
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Testing Posture',
                'title' => 'When this pays off the most',
                'summary' => 'Contract testing is strongest when the payload really is the transport boundary and the team wants broad negative coverage without a pile of custom boilerplate.',
                'rules' => [
                    'Use Strict or Paranoid profiles on auth, money, or mutation endpoints where malformed input should never leak into handler logic.',
                    'Keep the payload as the real boundary object; contract testing loses value if the handler still parses raw arrays or globals.',
                    'Prefer one generated suite plus strategy profiles over dozens of repetitive “422 when field is missing” tests.',
                    'Let the contract suite complement domain tests, not replace them; payload robustness and business behavior are different concerns.',
                ],
            ]);
    }
}
