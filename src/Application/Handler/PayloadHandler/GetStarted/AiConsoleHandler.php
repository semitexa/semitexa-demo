<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\AiConsolePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: AiConsolePayload::class, resource: DemoFeatureResource::class)]
final class AiConsoleHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(AiConsolePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'Semitexa can expose an AI-assisted console entrypoint through `bin/semitexa ai`, so routine CLI work does not always start from remembering the exact command name.',
            'how' => 'Instead of typing the final command directly, you can describe the intent in plain language. The assistant translates that request into the relevant Semitexa CLI operation and guides or executes the flow from the framework command surface.',
            'why' => 'This lowers the memory burden for real project work. Engineers can stay focused on intent such as flushing cache, restarting the runtime, or inspecting routes, while the framework still keeps the underlying operational path explicit.',
            'keywords' => [
                ['term' => 'bin/semitexa ai', 'definition' => 'Local assistant entrypoint for AI-guided CLI work.'],
                ['term' => 'natural-language prompt', 'definition' => 'A plain request like “Flush all cache please” instead of recalling the exact command syntax first.'],
                ['term' => 'experimental', 'definition' => 'Useful already, but still a developing workflow that should not replace understanding the real commands underneath.'],
                ['term' => 'command translation', 'definition' => 'Turning a human request into a concrete Semitexa console action or guided sequence.'],
            ],
        ];

        return $resource
            ->pageTitle('AI Console — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'ai-console',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('ai-console')
            ->withTitle('AI Console')
            ->withSummary('Use `bin/semitexa ai` as an alternative CLI entrypoint when you do not want to remember exact command names.')
            ->withEntryLine('For common maintenance and discovery work you can ask the CLI in plain language instead of recalling every exact command from memory.')
            ->withHighlights(['bin/semitexa ai', 'natural-language prompts', 'experimental', 'command translation'])
            ->withLearnMoreLabel('See the AI console flow →')
            ->withDeepDiveLabel('When to use it and when not to →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig', [
                'eyebrow' => 'Alternative CLI Path',
                'title' => 'Use intent first, not command recall',
                'summary' => 'If you roughly know what you need but do not remember the exact command, open `bin/semitexa ai` and describe the job in plain language. The workflow is already practical, but still experimental enough that teams should treat it as a guided operator layer, not magic.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Open the assistant entrypoint',
                        'summary' => 'Start from the Semitexa CLI instead of a separate external tool. This keeps the assistant close to the real runtime and command surface.',
                        'commands' => [
                            'bin/semitexa ai',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Describe the intent in plain language',
                        'summary' => 'You do not need the exact command name first. Ask for the operation you want, then let the assistant map that intent to the relevant CLI workflow.',
                        'commands' => [
                            'Flush all cache please.',
                            'Restart the local runtime and verify routes.',
                            'Show me the commands for inspecting tenant configuration.',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Review the translated action',
                        'summary' => 'The point is not to hide the real command forever. The point is to get to the correct Semitexa operation faster, with less memory tax and less grep-driven guessing.',
                        'commands' => [
                            'cache:clear',
                            'server:restart',
                            'routes:list',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Quiet Caveat',
                    'rules' => [
                        'This path is experimental: useful today, but still not the only workflow you should rely on.',
                        'Use it to reduce command-memory burden, not to stay ignorant of the real operator surface.',
                        'For repeatable team runbooks, keep documenting the concrete Semitexa commands underneath.',
                        'When you need the full machine-readable AI surface, continue to the CLI page about AI Tooling Surface.',
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Good Use',
                'title' => 'Where this helps most',
                'summary' => 'The AI console is strongest when intent is obvious but exact syntax is not. It is a convenience layer over the CLI, not a replacement for operational discipline.',
                'rules' => [
                    'Use it when you know the task but do not remember whether the command is `cache:clear`, `server:reload`, or something else nearby.',
                    'Use it for onboarding so new engineers can ask the framework what they mean instead of memorizing everything on day one.',
                    'Prefer direct commands in scripts, CI, and team docs where exact repeatability matters more than conversational entry.',
                    'If the request is risky or ambiguous, slow down and verify the concrete command before running it.',
                ],
            ])
            ->withExplanation($explanation);
    }
}
