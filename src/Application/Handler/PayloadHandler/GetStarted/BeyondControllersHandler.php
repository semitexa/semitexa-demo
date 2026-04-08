<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\GetStarted;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\GetStarted\BeyondControllersPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: BeyondControllersPayload::class, resource: DemoFeatureResource::class)]
final class BeyondControllersHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(BeyondControllersPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = [
            'what' => 'Semitexa deliberately avoids the ordinary controller as the main architectural unit. The transport contract lives in the payload, the use case lives in the handler, and the outgoing page or JSON shape lives in the resource.',
            'how' => 'A route with a real slug parameter is declared on the payload DTO with #[AsPayload]. Regex requirements, defaults, normalization, and validation live on that transport object before the handler runs. The handler then receives one trusted request object and one typed response resource instead of re-solving HTTP concerns in action code.',
            'why' => 'Controllers made sense when the goal was to move logic out of front controllers. They age badly once applications need modularity, stronger request contracts, SSR composition, machine-readable route metadata, and safe long-running runtime behavior. Semitexa splits those concerns so each one stays explicit and toolable.',
            'keywords' => [
                ['term' => 'Payload DTO', 'definition' => 'Owns the HTTP contract: path, methods, input shape, validation boundary, and route metadata.'],
                ['term' => 'TypedHandlerInterface', 'definition' => 'Owns the use-case step for one payload/resource pair without also acting as transport glue.'],
                ['term' => 'Resource DTO', 'definition' => 'Owns the response shape and rendering contract instead of letting handlers improvise arrays or inline responses.'],
                ['term' => 'controller drift', 'definition' => 'The failure mode where one controller class quietly accumulates routing, validation, auth, orchestration, and rendering concerns.'],
            ],
        ];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/GetStarted/ProductShowcasePayload.example.php',
            ),
            'Handler' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/GetStarted/ProductShowcaseHandler.example.php',
            ),
            'Resource' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/GetStarted/ProductShowcaseResource.example.php',
            ),
            'Legacy Controller Example' => $this->sourceCodeReader->readProjectRelativeSource(
                'resources/examples/GetStarted/LegacyProductShowController.example.php',
            ),
        ];

        return $resource
            ->pageTitle('Beyond Controllers — Semitexa Framework')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'get-started',
                'currentSlug' => 'beyond-controllers',
                'infoWhat' => $explanation['what'],
                'infoHow' => $explanation['how'],
                'infoWhy' => $explanation['why'],
                'infoKeywords' => $explanation['keywords'],
            ])
            ->withSection('get-started')
            ->withSectionLabel('Start Here')
            ->withSlug('beyond-controllers')
            ->withTitle('Beyond Controllers')
            ->withSummary('Controller-first design bundles too many responsibilities into one unstable class. Semitexa splits the transport contract, the use case, and the response shape deliberately.')
            ->withEntryLine('If one class owns the route, request parsing, validation, auth assumptions, business orchestration, and response assembly, it stops being simple and starts being the hidden coupling point of the whole feature.')
            ->withHighlights(['Payload owns slug contract', 'Handler owns use case', 'Resource owns response shape', 'No controller glue'])
            ->withLearnMoreLabel('See why controllers stop scaling →')
            ->withDeepDiveLabel('How the Semitexa split stays reviewable →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Architecture Contrast',
                'title' => 'A controller is one object doing yesterday\'s whole HTTP stack',
                'summary' => 'Semitexa is not anti-class. It is anti-collapse. The example payload below owns a real `{slug}` route parameter, its regex guard, normalization, and validation before the handler even starts business work.',
                'columns' => ['Concern', 'Typical controller-first class', 'Semitexa canonical owner'],
                'rows' => [
                    [
                        ['text' => 'Route contract'],
                        ['text' => 'Annotation or controller method metadata'],
                        ['text' => 'Payload DTO', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Input boundary'],
                        ['text' => 'Request object + ad hoc reads'],
                        ['text' => 'Payload setters and validation', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Use case orchestration'],
                        ['text' => 'Controller action body'],
                        ['text' => 'Typed handler', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Response shape'],
                        ['text' => 'Inline arrays / Response building'],
                        ['text' => 'Resource DTO', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Extensibility'],
                        ['text' => 'Middleware, helper traits, controller inheritance'],
                        ['text' => 'Explicit contracts and modules', 'variant' => 'success'],
                    ],
                ],
                'paragraphs' => [
                    'The controller pattern feels compact only while the endpoint is trivial.',
                    'As soon as route parameters, input rules, auth rules, response variants, and SSR composition appear, the controller becomes a mixed-concern shell that is harder to test, harder to extend, and harder for tooling to explain.',
                ],
                'note' => 'Semitexa keeps the HTTP boundary typed so route discovery, validation, response decoration, and introspection can all reason about the same declared contract.',
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Why Controller-First Ages Badly',
                'title' => 'Common failure modes that look normal until the codebase grows',
                'summary' => 'The problem is not the word "controller". The problem is using one class as the accidental dumping ground for every HTTP concern.',
                'rules' => [
                    'Validation logic leaks into action methods because the transport contract is not a first-class object.',
                    'Route parameters such as slugs are often trimmed, sanitized, defaulted, and rejected ad hoc in the controller body instead of at the payload boundary.',
                    'Response shape drifts between arrays, Response objects, view models, and template variables.',
                    'Route metadata becomes harder to inspect because it is attached to controller actions, middleware, and framework conventions at the same time.',
                    'Module extension gets coarse-grained because replacing a small behavior often means replacing the whole controller action or wrapping it indirectly.',
                    'LLM and static-analysis tooling see one mixed blob instead of a typed transport contract, a use case step, and a response contract.',
                ],
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig', [
                'eyebrow' => 'Semitexa Canon',
                'title' => 'What to remember when building the first real feature',
                'summary' => 'The Semitexa split is opinionated on purpose. It gives every layer one clear ownership boundary.',
                'rules' => [
                    'Put the route, slug parameter rules, and inbound data boundary on the payload DTO.',
                    'Keep the handler focused on the use case, not on request plumbing or manual response assembly.',
                    'Let the resource DTO own the outgoing page or machine response contract.',
                    'Treat controllers as a legacy compression pattern, not as the canonical unit of architecture.',
                    'When in doubt, ask which object should own the contract. In Semitexa, the answer is almost never "the controller".',
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
