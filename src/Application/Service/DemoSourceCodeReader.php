<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Support\ProjectRoot;

/**
 * Reads source files and extracts attribute metadata for display in the demo UI.
 *
 * Demo pages prefer curated snippets from resources/examples/ when available,
 * and fall back to reflection-based class source only for classes without an
 * explicit teaching-oriented example.
 */
#[AsService]
final class DemoSourceCodeReader
{
    /** @var array<string, string> */
    private const EXAMPLE_SOURCE_MAP = [
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Routing\\BasicRouteHandler' => 'resources/examples/Routing/BasicRoute/BasicRouteHandler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Routing\\BasicRoutePayload' => 'resources/examples/Routing/BasicRoute/BasicRoutePayload.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Routing\\ContentNegotiationHandler' => 'resources/examples/Routing/ContentNegotiation/ContentNegotiationHandler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Routing\\ContentNegotiationPayload' => 'resources/examples/Routing/ContentNegotiation/ContentNegotiationPayload.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Routing\\ParameterizedRouteHandler' => 'resources/examples/Routing/ParameterizedRoute/ParameterizedRouteHandler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Routing\\ParameterizedRoutePayload' => 'resources/examples/Routing/ParameterizedRoute/ParameterizedRoutePayload.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Auth\\ProtectedRouteHandler' => 'resources/examples/Auth/ProtectedRoute/ProtectedRouteHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Auth\\MachineAuthHandler' => 'resources/examples/Auth/MachineAuth/MachineAuthHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Auth\\RbacHandler' => 'resources/examples/Auth/Rbac/RbacHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Async\\SyncEventHandler' => 'resources/examples/Async/SyncEvents/SyncEventHandler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Event\\DemoItemCreated' => 'resources/examples/Async/SyncEvents/DemoItemCreated.example.php',
        'Semitexa\\Demo\\Application\\Handler\\DomainListener\\DemoItemCreatedListener' => 'resources/examples/Async/SyncEvents/DemoItemCreatedListener.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Async\\DeferredHandlerHandler' => 'resources/examples/Async/Deferred/DeferredHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\DomainListener\\DemoNotificationListener' => 'resources/examples/Async/Deferred/DemoNotificationListener.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Async\\QueuedHandlerHandler' => 'resources/examples/Async/Queued/QueuedHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Async\\SseStreamHandler' => 'resources/examples/Async/Sse/SseStreamHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Container\\DiOverviewHandler' => 'resources/examples/Container/DiOverview/DiOverviewHandler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Container\\DiOverviewPayload' => 'resources/examples/Container/DiOverview/DiOverviewPayload.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Container\\ReadonlyInjectionHandler' => 'resources/examples/Container/Readonly/ReadonlyInjectionHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Container\\MutableInjectionHandler' => 'resources/examples/Container/Mutable/MutableInjectionHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Container\\ServiceContractHandler' => 'resources/examples/Container/ServiceContract/ServiceContractHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Container\\FactoryInjectionHandler' => 'resources/examples/Container/Factory/FactoryInjectionHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Api\\ProductListV2Handler' => 'resources/examples/Api/Versioning/ProductListV2Handler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Api\\ProductListV2Payload' => 'resources/examples/Api/Versioning/ProductListV2Payload.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Api\\ProductListV0Handler' => 'resources/examples/Api/Versioning/ProductListV0Handler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Api\\ProductListV0Payload' => 'resources/examples/Api/Versioning/ProductListV0Payload.example.php',
        'Semitexa\\Demo\\Application\\Service\\DemoApiPresenter' => 'resources/examples/Api/Rest/DemoApiPresenter.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Api\\ApiSchemaDiscoveryHandler' => 'resources/examples/Api/Schema/ApiSchemaDiscoveryHandler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Api\\ApiSchemaDiscoveryPayload' => 'resources/examples/Api/Schema/ApiSchemaDiscoveryPayload.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Api\\ProductSchemaPayload' => 'resources/examples/Api/Schema/ProductSchemaPayload.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Api\\ProductListPayload' => 'resources/examples/Api/Rest/ProductListPayload.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Api\\ProductDetailPayload' => 'resources/examples/Api/Rest/ProductDetailPayload.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Api\\ApiErrorTriggerHandler' => 'resources/examples/Api/Errors/ApiErrorTriggerHandler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Api\\ApiErrorTriggerPayload' => 'resources/examples/Api/Errors/ApiErrorTriggerPayload.example.php',
        'Semitexa\\Demo\\Application\\Exception\\DemoApiNotFoundException' => 'resources/examples/Api/Errors/DemoApiNotFoundException.example.php',
        'Semitexa\\Api\\Pipeline\\ExternalApiExceptionMapper' => 'resources/examples/Api/Errors/ExternalApiExceptionMapper.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\SeoHandler' => 'resources/examples/Rendering/Seo/SeoHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\AiTaskSubmitHandler' => 'resources/examples/Rendering/AiTask/AiTaskSubmitHandler.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Request\\Rendering\\AiTaskSubmitPayload' => 'resources/examples/Rendering/AiTask/AiTaskSubmitPayload.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\DeferredScriptInjectionHandler' => 'resources/examples/Rendering/Deferred/DeferredSlotHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\DeferredEncapsulationHandler' => 'resources/examples/Rendering/Deferred/DeferredSlotHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\DeferredLiveWidgetsHandler' => 'resources/examples/Rendering/Deferred/DeferredSlotHandler.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Slot\\Deferred\\DeferredChartWidgetSlot' => 'resources/examples/Rendering/Deferred/DeferredSlot.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Slot\\Deferred\\DeferredCountdownSlot' => 'resources/examples/Rendering/Deferred/DeferredSlot.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Slot\\Deferred\\DeferredNotificationSlot' => 'resources/examples/Rendering/Deferred/DeferredSlot.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\ReactiveAiTaskHandler' => 'resources/examples/Rendering/Reactive/ReactiveSlotHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\ReactiveAnalyticsHandler' => 'resources/examples/Rendering/Reactive/ReactiveSlotHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\ReactiveImportHandler' => 'resources/examples/Rendering/Reactive/ReactiveSlotHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\ReactiveReportHandler' => 'resources/examples/Rendering/Reactive/ReactiveSlotHandler.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Slot\\Reactive\\ReactiveAiTaskSlot' => 'resources/examples/Rendering/Philosophy/ReactiveSlot.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Slot\\Reactive\\ReactiveAnalyticsSlot' => 'resources/examples/Rendering/Philosophy/ReactiveSlot.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Slot\\Reactive\\ReactiveImportSlot' => 'resources/examples/Rendering/Philosophy/ReactiveSlot.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Slot\\Reactive\\ReactiveReportSlot' => 'resources/examples/Rendering/Philosophy/ReactiveSlot.example.php',
        'Semitexa\\Demo\\Application\\Service\\DemoAiTextProcessor' => 'resources/examples/Rendering/Philosophy/Support/RecommendationService.example.php',
        'Semitexa\\Demo\\Application\\Service\\DemoAnalyticsAggregator' => 'resources/examples/Rendering/Philosophy/Support/AnalyticsService.example.php',
        'Semitexa\\Demo\\Application\\Service\\DemoProductImporter' => 'resources/examples/Rendering/Philosophy/Support/JobService.example.php',
        'Semitexa\\Demo\\Application\\Service\\DemoReportBuilder' => 'resources/examples/Rendering/Philosophy/Support/AnalyticsService.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\LayoutSlotHandler' => 'resources/examples/Rendering/LayoutSlot/LayoutSlotHandler.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Slot\\DemoNavSlot' => 'resources/examples/Rendering/SlotResources/DashboardSidebarSlot.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Rendering\\ResourceDtoHandler' => 'resources/examples/Rendering/ResourceDto/ProductShowcaseHandler.example.php',
        'Semitexa\\Demo\\Application\\Resource\\Response\\DemoFeatureResource' => 'resources/examples/Rendering/ResourceDto/ProductShowcaseResource.example.php',
        'Semitexa\\Demo\\Application\\Component\\DisclosurePromptComponent' => 'resources/examples/Rendering/Components/DisclosurePromptComponent.example.php',
        'Semitexa\\Demo\\Application\\Payload\\Event\\DemoDisclosureExpanded' => 'resources/examples/Rendering/Components/DemoDisclosureExpanded.example.php',
        'Semitexa\\Demo\\Application\\Handler\\DomainListener\\DemoDisclosureExpandedListener' => 'resources/examples/Rendering/Components/DemoDisclosureExpandedListener.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Data\\FilteringHandler' => 'resources/examples/Data/Catalog/ProductCatalogHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Data\\PaginationHandler' => 'resources/examples/Data/Catalog/ProductCatalogHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Data\\OrmCrudHandler' => 'resources/examples/Data/Catalog/ProductCatalogHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Data\\NPlusOneHandler' => 'resources/examples/Data/NPlusOne/NPlusOneHandler.example.php',
        'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\Data\\SharedTableExtensionHandler' => 'resources/examples/Data/SharedTable/SharedTableExtensionHandler.example.php',
        'Semitexa\\Demo\\Application\\Db\\MySQL\\Model\\DemoProductResource' => 'resources/examples/Data/Catalog/ProductResource.example.php',
        'Semitexa\\Demo\\Application\\Db\\MySQL\\Model\\DemoCategoryResource' => 'resources/examples/Data/Catalog/CategoryResource.example.php',
        'Semitexa\\Demo\\Application\\Db\\MySQL\\Repository\\DemoProductRepository' => 'resources/examples/Data/Catalog/ProductRepository.example.php',
        'Semitexa\\Api\\Domain\\Model\\MachineCredential' => 'resources/examples/Data/Identity/MachineCredential.example.php',
        'Semitexa\\Api\\Domain\\Contract\\MachineCredentialRepositoryInterface' => 'resources/examples/Data/Identity/MachineCredentialRepositoryInterface.example.php',
        'Semitexa\\Api\\Application\\Db\\MySQL\\Repository\\MachineCredentialRepository' => 'resources/examples/Data/Identity/MachineCredentialRepository.example.php',
        'Semitexa\\Api\\Application\\Db\\MySQL\\Model\\MachineCredentialTableModel' => 'resources/examples/Data/Identity/MachineCredentialTableModel.example.php',
        'Semitexa\\Api\\Application\\Db\\MySQL\\Model\\MachineCredentialMapper' => 'resources/examples/Data/Identity/MachineCredentialMapper.example.php',
    ];

    public function readClassSource(string $className): string
    {
        $exampleSource = $this->readExampleSourceForClass($className);
        if ($exampleSource !== null) {
            return $exampleSource;
        }

        $generatedSource = $this->generateDemoTeachingExample($className);
        if ($generatedSource !== '') {
            return $generatedSource;
        }

        if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);
        $fileName = $ref->getFileName();

        if ($fileName === false || !is_readable($fileName)) {
            return '';
        }

        $contents = file_get_contents($fileName);

        return $contents !== false ? $this->sanitizeForDisplay($contents) : '';
    }

    /**
     * @return list<\ReflectionAttribute<object>>
     */
    public function extractAttributes(string $className): array
    {
        if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
            return [];
        }

        $ref = new \ReflectionClass($className);

        return $ref->getAttributes();
    }

    public function readProjectRelativeSource(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');

        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return '';
        }

        foreach ($this->resolveReadableCandidates($relativePath) as $path) {
            $contents = file_get_contents($path);
            if ($contents !== false) {
                return $this->sanitizeForDisplay($contents);
            }
        }

        return '';
    }

    private function readExampleSourceForClass(string $className): ?string
    {
        $relativePath = self::EXAMPLE_SOURCE_MAP[$className] ?? null;
        if ($relativePath === null) {
            return null;
        }

        $relativePath = ltrim($relativePath, '/');
        foreach ($this->resolveReadableCandidates($relativePath) as $path) {
            if (!is_readable($path)) {
                continue;
            }

            $contents = file_get_contents($path);
            if ($contents !== false) {
                return $this->sanitizeForDisplay($contents);
            }
        }

        throw new \RuntimeException(sprintf(
            'Mapped curated example for %s is missing or unreadable: %s',
            $className,
            $relativePath,
        ));
    }

    private function generateDemoTeachingExample(string $className): string
    {
        return match (true) {
            str_starts_with($className, 'Semitexa\\Demo\\Application\\Handler\\PayloadHandler\\') => $this->renderPayloadHandlerExample($className),
            str_starts_with($className, 'Semitexa\\Demo\\Application\\Payload\\Request\\') => $this->renderRequestPayloadExample($className),
            str_starts_with($className, 'Semitexa\\Demo\\Application\\Payload\\Event\\') => $this->renderEventPayloadExample($className),
            str_starts_with($className, 'Semitexa\\Demo\\Application\\Payload\\') => $this->renderRequestPayloadExample($className),
            str_starts_with($className, 'Semitexa\\Demo\\Application\\Resource\\') => $this->renderResourceExample($className),
            str_starts_with($className, 'Semitexa\\Demo\\Application\\Handler\\DomainListener\\') => $this->renderDomainListenerExample($className),
            str_starts_with($className, 'Semitexa\\Demo\\Application\\Component\\') => $this->renderComponentExample($className),
            str_starts_with($className, 'Semitexa\\Demo\\Application\\Service\\') => $this->renderServiceExample($className),
            default => '',
        };
    }

    private function renderPayloadHandlerExample(string $className): string
    {
        if (!class_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);
        $method = $ref->hasMethod('handle') ? $ref->getMethod('handle') : null;
        $payloadType = $method !== null ? $this->resolveParameterClassName($method, 0) : null;
        $resourceType = $method !== null ? $this->resolveParameterClassName($method, 1) : null;
        $payloadClass = $payloadType ?? '\\App\\Application\\Payload\\Request\\ExamplePayload';
        $resourceClass = $resourceType ?? '\\App\\Application\\Resource\\Response\\PageResource';

        return $this->sanitizeForDisplay(<<<PHP
<?php

declare(strict_types=1);

namespace App\Application\Handler\PayloadHandler\Example;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: {$payloadClass}::class, resource: {$resourceClass}::class)]
final class {$ref->getShortName()} implements TypedHandlerInterface
{
    public function handle({$payloadClass} \$payload, {$resourceClass} \$resource): {$resourceClass}
    {
        return \$resource
            ->withTitle('Example feature')
            ->withSummary('Keep handlers small, declarative, and centered around the payload contract.');
    }
}
PHP);
    }

    private function renderRequestPayloadExample(string $className): string
    {
        if (!class_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);
        $slug = $this->kebabCase($ref->getShortName());

        return $this->sanitizeForDisplay(<<<PHP
<?php

declare(strict_types=1);

namespace App\Application\Payload\Request\Example;

use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Base\BasePayload;

#[AsPayload(methods: ['GET'], path: '/demo/example/{$slug}')]
final class {$ref->getShortName()} extends BasePayload
{
    public function getSearch(): string
    {
        return (string) \$this->getQueryValue('search', '');
    }
}
PHP);
    }

    private function renderEventPayloadExample(string $className): string
    {
        if (!class_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);

        return $this->sanitizeForDisplay(<<<PHP
<?php

declare(strict_types=1);

namespace App\Application\Payload\Event\Example;

final readonly class {$ref->getShortName()}
{
    public function __construct(
        public string \$aggregateId,
        public string \$actorId,
    ) {}
}
PHP);
    }

    private function renderResourceExample(string $className): string
    {
        if (!class_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);

        return $this->sanitizeForDisplay(<<<PHP
<?php

declare(strict_types=1);

namespace App\Application\Resource\Response\Example;

final class {$ref->getShortName()}
{
    public function withTitle(string \$title): self
    {
        return \$this;
    }

    public function withSummary(string \$summary): self
    {
        return \$this;
    }
}
PHP);
    }

    private function renderDomainListenerExample(string $className): string
    {
        if (!class_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);

        return $this->sanitizeForDisplay(<<<PHP
<?php

declare(strict_types=1);

namespace App\Application\Handler\DomainListener\Example;

final class {$ref->getShortName()}
{
    public function __invoke(object \$event): void
    {
        // Trigger side effects here without bloating the request handler.
    }
}
PHP);
    }

    private function renderComponentExample(string $className): string
    {
        if (!class_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);

        return $this->sanitizeForDisplay(<<<PHP
<?php

declare(strict_types=1);

namespace App\Application\Component\Example;

final class {$ref->getShortName()}
{
    public function getProps(): array
    {
        return [
            'title' => 'SSR-first component',
            'actionLabel' => 'Open',
        ];
    }
}
PHP);
    }

    private function renderServiceExample(string $className): string
    {
        if (!class_exists($className)) {
            return '';
        }

        $ref = new \ReflectionClass($className);

        return $this->sanitizeForDisplay(<<<PHP
<?php

declare(strict_types=1);

namespace App\Application\Service\Example;

final class {$ref->getShortName()}
{
    public function buildSummary(array \$input): array
    {
        return [
            'count' => count(\$input),
            'items' => \$input,
        ];
    }
}
PHP);
    }

    private function resolveParameterClassName(\ReflectionMethod $method, int $index): ?string
    {
        $parameters = $method->getParameters();
        if (!isset($parameters[$index])) {
            return null;
        }

        $type = $parameters[$index]->getType();
        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        return '\\' . ltrim($type->getName(), '\\');
    }

    private function kebabCase(string $value): string
    {
        $kebab = preg_replace('/(?<!^)[A-Z]/', '-$0', $value) ?? $value;

        return strtolower($kebab);
    }

    /**
     * @return list<string>
     */
    private function resolveReadableCandidates(string $relativePath): array
    {
        $candidates = [];
        $strippedDemoPath = null;

        if (str_starts_with($relativePath, 'packages/semitexa-demo/')) {
            $strippedDemoPath = substr($relativePath, strlen('packages/semitexa-demo/'));
        }

        foreach ($this->candidateRoots() as $root) {
            $root = rtrim($root, '/');
            $candidates[] = $this->resolveCandidateWithinRoot($root, $relativePath);

            if ($strippedDemoPath !== null && str_ends_with($root, '/packages/semitexa-demo')) {
                $candidates[] = $this->resolveCandidateWithinRoot(
                    $root,
                    $strippedDemoPath,
                );
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    /**
     * @return list<string>
     */
    private function candidateRoots(): array
    {
        $packageRoot = dirname(__DIR__, 3);
        $monorepoRoot = dirname($packageRoot, 2);

        return array_values(array_unique([
            rtrim(ProjectRoot::get(), '/'),
            rtrim($packageRoot, '/'),
            rtrim($monorepoRoot, '/'),
        ]));
    }

    private function resolveCandidateWithinRoot(string $root, string $relativePath): ?string
    {
        $path = realpath($root . '/' . $relativePath);
        if ($path === false || !is_file($path) || !str_starts_with($path, $root . '/') || !is_readable($path)) {
            return null;
        }

        return $path;
    }

    private function sanitizeForDisplay(string $contents): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $contents) ?: [];
        $filtered = [];
        $skippingDemoFeature = false;

        foreach ($lines as $line) {
            if (preg_match('/^use\s+Semitexa\\\\Demo\\\\Attributes\\\\DemoFeature;$/', trim($line)) === 1) {
                continue;
            }

            if (str_contains($line, '#[DemoFeature(')) {
                $skippingDemoFeature = true;
                continue;
            }

            if ($skippingDemoFeature) {
                if (trim($line) === ')]') {
                    $skippingDemoFeature = false;
                }

                continue;
            }

            $filtered[] = $line;
        }

        $sanitized = implode("\n", $filtered);

        return preg_replace("/\n{3,}/", "\n\n", $sanitized) ?? $sanitized;
    }
}
