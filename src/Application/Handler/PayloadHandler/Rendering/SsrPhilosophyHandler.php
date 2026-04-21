<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Rendering\SsrPhilosophyPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SsrPhilosophyPayload::class, resource: DemoFeatureResource::class)]
final class SsrPhilosophyHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(SsrPhilosophyPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'philosophy',
            entryLine: 'Semitexa SSR is not “render once on the server and then improvise”. It is a coherent rendering system that refuses both kinds of drift: backend HTML plus frontend survival code, and fake “data vs presentation” separation where templates quietly become data loaders.',
            learnMoreLabel: 'See the Semitexa SSR axioms →',
            deepDiveLabel: 'What Semitexa SSR refuses to become →',
            relatedSlugs: [],
            fallbackTitle: 'SSR Philosophy',
            fallbackSummary: 'Semitexa SSR is one continuous rendering architecture: page, slots, deferred regions, live refresh, and interactive components stay inside one server-owned story.',
            fallbackHighlights: ['one rendering story', 'HtmlResponse', 'Presentation boundary', 'Deferred SSR', 'Framework-free enhancement'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'philosophy') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Page Resource' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Rendering/Philosophy/PageResource.example.php'),
                'Slot Resource' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Rendering/Philosophy/SlotResource.example.php'),
                'Deferred Slot' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Rendering/Philosophy/DeferredSlot.example.php'),
                'Reactive Slot' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Rendering/Philosophy/ReactiveSlot.example.php'),
                'Interactive Component' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Rendering/Philosophy/EventComponent.example.php'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/ssr-manifesto.html.twig', [
                'painPoints' => [
                    '“SSR” usually means the first paint is server-rendered, but the moment the page becomes dynamic, teams quietly switch to a second frontend architecture.',
                    'At the same time, templates start accumulating query logic, ad hoc reshaping, and service calls because nobody protected the presentation boundary structurally.',
                    'Page regions become partials with hidden context, live widgets become little apps, and interaction drifts into custom fetch glue.',
                    'Soon the page is “SSR” only in name, while React, Alpine, or bespoke client glue becomes the real owner of interaction and state interpretation.',
                    'The result works, but the architecture has already split into two competing truths: server render for the shell, client orchestration for everything interesting.',
                ],
                'signals' => [
                    ['value' => '1', 'label' => 'rendering story from first byte to live update'],
                    ['value' => '1', 'label' => 'presentation boundary before Twig sees data'],
                    ['value' => '0', 'label' => 'client-side page takeover points'],
                    ['value' => '0', 'label' => 'required UI framework layers to make the page interactive'],
                    ['value' => '0', 'label' => 'template-level database or API fetching as an accepted pattern'],
                    ['value' => '6', 'label' => 'core Semitexa SSR primitives composing one model'],
                ],
                'compare' => [
                    [
                        'variant' => 'warning',
                        'eyebrow' => 'Typical Split SSR',
                        'title' => 'Server-render the shell, then smuggle in a client framework',
                        'summary' => 'The page starts on the server, but regions, refresh loops, and interactions slowly migrate into React, Alpine, or ad hoc client orchestration.',
                        'note' => 'You keep the phrase SSR, but the browser quietly becomes the second rendering authority.',
                    ],
                    [
                        'variant' => 'active',
                        'eyebrow' => 'Semitexa SSR',
                        'title' => 'One rendering architecture, end to end',
                        'summary' => 'Page resource, slot resources, deferred delivery, reactive refresh, component event bridging, and tiny component-owned scripts all remain part of the same server-owned rendering model.',
                        'note' => 'The UI can become late, live, or interactive without making a client framework the hidden second half of the system.',
                    ],
                ],
                'pillars' => [
                    ['title' => 'Page Contract', 'detail' => 'Resource DTOs make the page response explicit before Twig sees a single field.'],
                    ['title' => 'Presentation Boundary', 'detail' => 'Templates consume prepared data instead of querying storage, calling APIs, or inventing new view-side mapping rules.'],
                    ['title' => 'Region Contract', 'detail' => 'Slots are real resources with their own pipeline, not fragment glue.'],
                    ['title' => 'Late HTML', 'detail' => 'Deferred blocks stream SSR HTML later instead of handing control to a client renderer.'],
                    ['title' => 'Live HTML', 'detail' => 'Reactive slots refresh from server truth without inventing a second state machine.'],
                    ['title' => 'Interactive HTML', 'detail' => 'Components can now dispatch backend events while staying in the SSR component model.'],
                    ['title' => 'Framework-Free JS', 'detail' => 'When JavaScript is needed, it stays as small component-owned enhancement rather than a mandatory UI framework layer.'],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/ssr-manifesto-rules.html.twig', [
                'rules' => [
                    'The page response must be explicit before template rendering begins.',
                    'Templates are presentation surfaces, not a place to fetch from databases, hit APIs, or smuggle handler logic into Twig.',
                    'A region is a resource with a pipeline, not a partial with lucky context.',
                    'Deferred does not mean “replace SSR with client rendering later”. It means late server HTML.',
                    'Reactive does not mean “invent a mini SPA for one widget”. It means the server keeps re-rendering truth.',
                    'Interactivity does not require escaping into bespoke API glue. Components can declare backend event contracts directly.',
                    'JavaScript may enhance the page, but Semitexa refuses to require React, Alpine, Angular, or another client framework as the second rendering layer.',
                    'Assets, scripts, and SEO stay inside the same rendering system instead of becoming a parallel deployment concern.',
                ],
                'checks' => [
                    ['label' => 'HtmlResponse', 'detail' => 'Page-level response object accumulates the render contract before Twig.'],
                    ['label' => 'Resource DTOs', 'detail' => 'Handlers shape presentation data before it reaches the template, which blocks template-side data drift at the architectural boundary.'],
                    ['label' => 'Slot Resources', 'detail' => 'Page regions use the same response discipline as the page itself.'],
                    ['label' => 'Deferred Blocks', 'detail' => 'Slow regions arrive later as HTML over SSE, not as client-owned redraw logic.'],
                    ['label' => 'Reactive Slots', 'detail' => 'Live UI keeps re-rendering server truth instead of mirroring it in a frontend state graph.'],
                    ['label' => 'Component Event Bridge', 'detail' => 'Interactive components can trigger backend events without abandoning the SSR component contract.'],
                    ['label' => 'Component Script Assets', 'detail' => 'Small component-owned JavaScript is allowed, but only as scoped enhancement rather than as a framework takeover.'],
                ],
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/ssr-manifesto-antipatterns.html.twig', [
                'mapping' => [
                    ['anti' => '“SSR for the first paint, client app for everything real.”', 'answer' => 'Semitexa keeps deferred and reactive regions inside the same HTML pipeline.'],
                    ['anti' => '“The template can just query what it needs, call an API, or reshape the data inline.”', 'answer' => 'Semitexa treats that as presentation-boundary failure. Handlers and resource DTOs must shape data before Twig sees it.'],
                    ['anti' => '“This region is just a partial, pass whatever data it seems to need.”', 'answer' => 'Semitexa promotes regions to slot resources with an explicit render contract.'],
                    ['anti' => '“The widget is live, so we need a second state architecture.”', 'answer' => 'Semitexa refreshes the slot from server truth and swaps HTML in place.'],
                    ['anti' => '“This interaction needs fetch glue, custom endpoints, and manual wiring.”', 'answer' => 'Semitexa components can now declare backend event contracts and dispatch through one generic bridge.'],
                    ['anti' => '“Once the page becomes interactive, we obviously need React, Alpine, or another client UI framework.”', 'answer' => 'Semitexa rejects that as a default assumption. Small component-owned scripts are enough when the server still owns rendering truth.'],
                    ['anti' => '“SEO, assets, scripts, templates, and live behavior all belong to different systems.”', 'answer' => 'Semitexa treats them as one rendering surface owned by one framework model.'],
                ],
            ]);
    }
}
