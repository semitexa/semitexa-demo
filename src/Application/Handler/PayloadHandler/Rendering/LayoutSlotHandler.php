<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\LayoutSlotPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\DemoFeatureInfoSlot;
use Semitexa\Demo\Application\Resource\Slot\DemoNavSlot;
use Semitexa\Demo\Application\Resource\Slot\DemoSidebarSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: LayoutSlotPayload::class, resource: DemoFeatureResource::class)]
final class LayoutSlotHandler implements TypedHandlerInterface
{
    private const DEMO_SLOTS = [
        ['slot' => 'demo_nav',          'class' => DemoNavSlot::class,         'handle' => 'demo', 'desc' => 'Top navigation bar'],
        ['slot' => 'demo_sidebar',      'class' => DemoSidebarSlot::class,     'handle' => 'demo', 'desc' => 'Left feature tree'],
        ['slot' => 'demo_feature_info', 'class' => DemoFeatureInfoSlot::class, 'handle' => 'demo', 'desc' => 'Right info panel (What/How/Why)'],
    ];

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(LayoutSlotPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $rows = '';
        foreach (self::DEMO_SLOTS as $slot) {
            $rows .= sprintf(
                '<tr><td><code>%s</code></td><td><code>%s</code></td><td>%s</td><td><code>{{ layout_slot(\'%s\') }}</code></td></tr>',
                htmlspecialchars($slot['slot']),
                htmlspecialchars(basename(str_replace('\\', '/', $slot['class']))),
                htmlspecialchars($slot['desc']),
                htmlspecialchars($slot['slot']),
            );
        }

        $resultPreview = '<div class="result-preview">'
            . '<p>The demo layout has <strong>3 named slots</strong>. Each is filled by an independent '
            . '<code>#[AsSlotResource]</code> — they have no knowledge of each other.</p>'
            . '<table class="data-table">'
            . '<thead><tr><th>Slot name</th><th>Resource class</th><th>Purpose</th><th>Layout call</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "#[AsSlotResource(handle: 'demo', slot: 'demo_nav',\n"
                . "    template: '@project-layouts-semitexa-demo/partials/nav.html.twig')]\n"
                . "class DemoNavSlot extends HtmlSlotResponse { ... }"
            )
            . '</pre>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'slots') ?? [];

        $sourceCode = [
            'Slot Resource' => $this->sourceCodeReader->readClassSource(DemoNavSlot::class),
        ];

        return $resource
            ->pageTitle('Layout Slots — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('slots')
            ->withTitle('Layout Slots')
            ->withSummary('Fill named layout regions from independent slot resources — zero coupling between page regions.')
            ->withEntryLine('Fill named layout regions from independent slot resources — zero coupling between page regions.')
            ->withHighlights(['#[AsSlotResource]', 'HtmlSlotResponse', 'layout_slot()', 'slot handle', 'independent rendering'])
            ->withLearnMoreLabel('See slot registration →')
            ->withDeepDiveLabel('Slot resolution order →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
