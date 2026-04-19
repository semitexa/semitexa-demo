<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

#[AsService]
final class DemoFeatureCompanionResolver
{
    public function resolve(string $section, string $slug, ?DemoFeatureDocument $document): DemoFeatureCompanion
    {
        return match ($section . '/' . $slug) {
            'get-started/installation' => $this->installationCompanion(),
            'get-started/local-domain' => $this->localDomainCompanion(),
            'get-started/module-structure' => $this->moduleStructureCompanion(),
            'get-started/base-tenant' => $this->baseTenantCompanion(),
            'get-started/locale-setup' => $this->localeSetupCompanion(),
            'get-started/ai-console' => $this->aiConsoleCompanion(),
            'get-started/beyond-controllers' => $this->beyondControllersCompanion(),
            default => new DemoFeatureCompanion(),
        };
    }

    private function installationCompanion(): DemoFeatureCompanion
    {
        return new DemoFeatureCompanion(
            resultPreviewTemplate: '@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig',
            resultPreviewData: [
                'eyebrow' => 'Canonical Install',
                'title' => 'Create the project and get to a trustworthy first boot',
                'summary' => 'Use the installer, review the shared env baseline, boot the runtime with the Semitexa CLI, and verify the project shape before you start authoring modules.',
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
                        'title' => 'Review the env boundary',
                        'summary' => 'Semitexa now writes `.env.default` as the committed baseline. Edit `.env` only when this machine needs overrides such as a different port.',
                        'commands' => [
                            'cd my-project',
                            '$EDITOR .env',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Boot and verify',
                        'summary' => 'Use the operator shell to start, check, and inspect the runtime instead of guessing from container state alone. After boot, open the local app in the browser and confirm you have a real page, not just a running container.',
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
                        'The default app URL is http://localhost:9502 unless `SWOOLE_PORT` is overridden in `.env`.',
                        'If you installed Semitexa Demo during setup, open http://localhost:9502/demo after boot to inspect working feature pages immediately.',
                        'You do not need host PHP or Composer for the normal local flow.',
                    ],
                ],
            ],
            l2ContentTemplate: '@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig',
            l2ContentData: [
                'eyebrow' => 'Right After Install',
                'title' => 'Do these checks before you touch business code',
                'summary' => 'A clean first boot should be operationally legible. Use the CLI to confirm health, discovery, and current bindings before you start extending the project.',
                'rules' => [
                    'Run `bin/semitexa self-test` when startup feels suspicious instead of debugging blind.',
                    'Use `bin/semitexa logs:errors` as the first log command when the runtime did not boot cleanly.',
                    'Inspect `bin/semitexa ai:ask project --json` and `bin/semitexa contracts:list --json` early so you know what the project discovered.',
                    'If ORM-backed modules are active, treat `bin/semitexa orm:sync --dry-run` as part of the normal first setup path.',
                ],
            ],
            l3ContentTemplate: '@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig',
            l3ContentData: [
                'eyebrow' => 'Boot Verification',
                'title' => 'What a clean first boot should prove',
                'summary' => 'After `bin/semitexa server:start`, you should be able to verify the runtime from both the browser and the operator shell without guessing what happened.',
                'rules' => [
                    'Open `http://localhost:9502` and confirm the app responds with the starter page instead of a Docker, proxy, or browser error.',
                    'If Semitexa Demo was installed, open `http://localhost:9502/demo` and confirm the demo home renders before you explore deeper pages.',
                    'Run `bin/semitexa self-test` and expect a clean health check before you start debugging application code.',
                    'Run `bin/semitexa routes:list --json` so route discovery is visible instead of assumed.',
                    'Use `bin/semitexa ai:ask project --json` when you need to inspect what the scaffold and installed modules actually registered.',
                ],
            ],
        );
    }

    private function localDomainCompanion(): DemoFeatureCompanion
    {
        return new DemoFeatureCompanion(
            resultPreviewTemplate: '@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig',
            resultPreviewData: [
                'eyebrow' => 'Local Host Strategy',
                'title' => 'Make local URLs look like product hosts',
                'summary' => 'Choose one `.test` base domain, register hosts through the Semitexa local-domain helper, and restart so the runtime resolves them predictably.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Choose the base domain',
                        'summary' => 'Keep the local base memorable and stable. The common example is `semitexa.test`.',
                        'commands' => [
                            'TENANCY_BASE_DOMAIN=semitexa.test',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Register the browser hosts',
                        'summary' => 'Register both the main host and any tenant-specific host you plan to open during local work.',
                        'commands' => [
                            'bin/semitexa local-domain:add semitexa.test',
                            'bin/semitexa local-domain:add acme.semitexa.test',
                            'bin/semitexa local-domain:list',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Restart and open the host',
                        'summary' => 'After domain changes, restart once so the runtime, proxy, and DNS layer agree on the same environment.',
                        'commands' => [
                            'bin/semitexa server:restart',
                            'http://semitexa.test',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Why Not /etc/hosts?',
                    'rules' => [
                        'The CLI-managed local-domain path is repeatable across projects and easier to inspect later with `local-domain:list`.',
                        'It keeps the local proxy and DNS story in the same operator surface instead of splitting it between shell scripts and OS edits.',
                        'It makes tenant hosts easier to add and remove as the project evolves.',
                    ],
                ],
            ],
            l2ContentTemplate: '@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig',
            l2ContentData: [
                'eyebrow' => 'Practical Rules',
                'title' => 'Keep local domain work boring and consistent',
                'summary' => 'The point of local domains is to reduce ambiguity, not to create a custom networking hobby project.',
                'rules' => [
                    'Prefer one `.test` base domain per project instead of mixing many local suffixes.',
                    'Register domains with `bin/semitexa local-domain:add` and remove stale ones with `bin/semitexa local-domain:remove`.',
                    'Restart after meaningful DNS or `.env` changes so the runtime shape stays coherent.',
                    'Open the real host in the browser once local DNS is in place, not only `localhost:9502`.',
                ],
            ],
        );
    }

    private function moduleStructureCompanion(): DemoFeatureCompanion
    {
        return new DemoFeatureCompanion(
            resultPreviewTemplate: '@project-layouts-semitexa-demo/components/previews/module-structure-files.html.twig',
            resultPreviewData: [
                'title' => 'File tree: a contact form module example',
                'summary' => 'Each file points to the page that explains that concern in more detail.',
                'tree' => <<<'TREE'
packages/semitexa-demo/src/Application/
├── Payload/
│   └── Request/
│       └── <a href="/demo/routing/payload-shield">ContactFormPayload.php</a>
├── Handler/
│   └── PayloadHandler/
│       └── <a href="/demo/get-started/beyond-controllers">ContactFormHandler.php</a>
├── Resource/
│   └── Response/
│       └── <a href="/demo/rendering/resource-dtos">ContactFormResource.php</a>
└── View/
    └── templates/
        └── pages/
            └── <a href="/demo/rendering/resource-dtos">contact-form.html.twig</a>
TREE,
            ],
        );
    }

    private function baseTenantCompanion(): DemoFeatureCompanion
    {
        return new DemoFeatureCompanion(
            resultPreviewTemplate: '@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig',
            resultPreviewData: [
                'eyebrow' => 'Tenant Bootstrap',
                'title' => 'Add the first tenant to `.env` and open it through a real host',
                'summary' => 'There is no separate bootstrap wizard required for the normal local flow. Define the tenant in environment variables, register the host, restart, and open it in the browser.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Declare the tenant in `.env`',
                        'summary' => 'Use one tenant id prefix and keep the first tenant shape explicit.',
                        'commands' => [
                            'TENANCY_BASE_DOMAIN=semitexa.test',
                            'TENANT_ACME_NAME=Acme Workspace',
                            'TENANT_ACME_STATUS=active',
                            'TENANT_ACME_DOMAIN=acme.semitexa.test',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Register the tenant host',
                        'summary' => 'The domain must exist in the local DNS helper as well as in the tenancy configuration.',
                        'commands' => [
                            'bin/semitexa local-domain:add acme.semitexa.test',
                            'bin/semitexa server:restart',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Open the tenant',
                        'summary' => 'Browse the tenant host directly so resolution happens through the real request host.',
                        'commands' => [
                            'http://acme.semitexa.test',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Useful Variants',
                    'rules' => [
                        'Use `TENANT_<ID>_DOMAINS` when the same tenant should answer to more than one local host.',
                        'Use `TENANT_<ID>_PUBLIC_DOMAIN` and `TENANT_<ID>_PUBLIC_DOMAINS` when you separately model public production hosts.',
                        'The tenant id is the part between `TENANT_` and the field suffix. `TENANT_ACME_*` becomes tenant id `acme`.',
                    ],
                ],
            ],
            l2ContentTemplate: '@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig',
            l2ContentData: [
                'eyebrow' => 'Resolution Rules',
                'title' => 'What makes the first tenant reliable',
                'summary' => 'A tenant should be easy to explain from configuration alone. If it takes custom lore to know which host resolves to which tenant, the local setup is already too fragile.',
                'rules' => [
                    'Keep tenant ids stable and human-readable because they become part of local and operational reasoning.',
                    'Prefer one clear primary `TENANT_<ID>_DOMAIN` before introducing additional aliases.',
                    'When tenant resolution looks wrong, check `.env`, then `local-domain:list`, then the actual browser host in that order.',
                    'Use the Tenancy pages after this setup if you want to inspect how Semitexa resolves and propagates tenant context internally.',
                ],
            ],
        );
    }

    private function localeSetupCompanion(): DemoFeatureCompanion
    {
        return new DemoFeatureCompanion(
            resultPreviewTemplate: '@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig',
            resultPreviewData: [
                'eyebrow' => 'Locale Baseline',
                'title' => 'Add the smallest locale setup that is still real',
                'summary' => 'Declare the locale boundary in `.env`, add one JSON file per language, render one translated string through Twig, and restart once so the catalogs reload cleanly.',
                'steps' => [
                    [
                        'eyebrow' => 'Step 1',
                        'title' => 'Declare the locale contract in `.env`',
                        'summary' => 'Keep the first setup tiny. You usually need only the default locale, fallback locale, supported locales, and whether locale prefixes belong in URLs.',
                        'commands' => [
                            'LOCALE_DEFAULT=en',
                            'LOCALE_FALLBACK=en',
                            'LOCALE_SUPPORTED=en,uk',
                            'LOCALE_URL_PREFIX=false',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 2',
                        'title' => 'Add one JSON catalog per language',
                        'summary' => 'Locale files are module-local. Start with one or two keys, not a giant spreadsheet dump.',
                        'commands' => [
                            'src/Application/View/locales/en.json',
                            '{"ContactForm.title":"Contact us","ContactForm.submit":"Send"}',
                            'src/Application/View/locales/uk.json',
                            '{"ContactForm.title":"Zvjazhit`sja z namy","ContactForm.submit":"Nadislaty"}',
                        ],
                    ],
                    [
                        'eyebrow' => 'Step 3',
                        'title' => 'Render and verify one translated string',
                        'summary' => 'Use `trans()` in Twig, restart once so the worker reloads catalogs, then verify the output through the locale path or request headers.',
                        'commands' => [
                            "{{ trans('ContactForm.title') }}",
                            'bin/semitexa server:restart',
                            "curl -H 'Accept-Language: uk' http://semitexa.test",
                            'http://semitexa.test',
                        ],
                    ],
                ],
                'callout' => [
                    'eyebrow' => 'Important Defaults',
                    'rules' => [
                        'If you installed from `semitexa/ultimate`, the locale package is already part of the baseline stack.',
                        'JSON locale catalogs are loaded at worker boot, so restart or reload after editing them.',
                        'Turn `LOCALE_URL_PREFIX=true` on only when you really want locale-prefixed URLs like `/uk/...`.',
                        'Keep keys module-scoped from day one. `ContactForm.title` ages much better than `title`.',
                    ],
                ],
            ],
            l2ContentTemplate: '@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig',
            l2ContentData: [
                'eyebrow' => 'Minimal Means Reviewable',
                'title' => 'What the first locale pass should already prove',
                'summary' => 'The first locale setup does not need plural rules, per-tenant overrides, or a language switcher yet. It only needs to prove that the contract is real and inspectable.',
                'rules' => [
                    'Keep `LOCALE_SUPPORTED` short until you have real copy for each language.',
                    'Use one translated Twig key as a smoke test before you spread `trans()` across the app.',
                    'If locale output looks wrong, check `.env`, then the JSON file path, then restart the worker before debugging deeper.',
                    'When tenant-specific locale defaults matter, continue with the Tenancy and platform locale pages after this baseline is working.',
                ],
            ],
        );
    }

    private function aiConsoleCompanion(): DemoFeatureCompanion
    {
        return new DemoFeatureCompanion(
            resultPreviewTemplate: '@project-layouts-semitexa-demo/components/previews/get-started-playbook.html.twig',
            resultPreviewData: [
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
            ],
            l2ContentTemplate: '@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig',
            l2ContentData: [
                'eyebrow' => 'Good Use',
                'title' => 'Where this helps most',
                'summary' => 'The AI console is strongest when intent is obvious but exact syntax is not. It is a convenience layer over the CLI, not a replacement for operational discipline.',
                'rules' => [
                    'Use it when you know the task but do not remember whether the command is `cache:clear`, `server:reload`, or something else nearby.',
                    'Use it for onboarding so new engineers can ask the framework what they mean instead of memorizing everything on day one.',
                    'Prefer direct commands in scripts, CI, and team docs where exact repeatability matters more than conversational entry.',
                    'If the request is risky or ambiguous, slow down and verify the concrete command before running it.',
                ],
            ],
        );
    }

    private function beyondControllersCompanion(): DemoFeatureCompanion
    {
        return new DemoFeatureCompanion(
            resultPreviewTemplate: '@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig',
            resultPreviewData: [
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
            ],
            l2ContentTemplate: '@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig',
            l2ContentData: [
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
            ],
            l3ContentTemplate: '@project-layouts-semitexa-demo/components/previews/checklist-panel.html.twig',
            l3ContentData: [
                'eyebrow' => 'Semitexa Canon',
                'title' => 'What to remember when building the first real feature',
                'summary' => 'Once the transport boundary, use case, and response each have an owner, the rest of the framework becomes easier to inspect, extend, and automate.',
                'rules' => [
                    'Declare the route, HTTP methods, and payload boundary on the payload DTO, not in a controller method signature.',
                    'Keep the handler focused on orchestration and use-case flow, not transport parsing or response assembly details.',
                    'Let the resource own response shape, SEO metadata, and template context so rendering stays explicit and reusable.',
                    'Add modules and contracts around this typed spine instead of reopening one growing action class for every new concern.',
                ],
            ],
        );
    }
}
