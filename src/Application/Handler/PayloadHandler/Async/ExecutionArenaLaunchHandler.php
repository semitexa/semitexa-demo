<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Event\EventDispatcherInterface;
use Semitexa\Core\Http\HttpStatus;
use Semitexa\Demo\Application\Payload\Event\DemoExecutionShowcaseAsyncRequested;
use Semitexa\Demo\Application\Payload\Event\DemoExecutionShowcaseQueuedRequested;
use Semitexa\Demo\Application\Payload\Event\DemoExecutionShowcaseSyncRequested;
use Semitexa\Demo\Application\Payload\Request\Async\ExecutionArenaLaunchPayload;
use Semitexa\Demo\Application\Resource\Response\DemoJsonResource;
use Semitexa\Demo\Application\Service\DemoExecutionShowcaseService;

#[AsPayloadHandler(payload: ExecutionArenaLaunchPayload::class, resource: DemoJsonResource::class)]
final class ExecutionArenaLaunchHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoExecutionShowcaseService $showcaseService;

    #[InjectAsReadonly]
    protected EventDispatcherInterface $eventDispatcher;

    public function handle(ExecutionArenaLaunchPayload $payload, DemoJsonResource $resource): DemoJsonResource
    {
        $mode = $payload->getMode();
        $sessionId = trim((string) $payload->getSessionId());

        if (!$this->showcaseService->isSupportedMode($mode)) {
            return $resource
                ->setStatusCode(HttpStatus::UnprocessableEntity->value)
                ->withData([
                    'ok' => false,
                    'error' => 'Unsupported mode.',
                ]);
        }

        if ($sessionId === '') {
            return $resource
                ->setStatusCode(HttpStatus::UnprocessableEntity->value)
                ->withData([
                    'ok' => false,
                    'error' => 'Missing SSE session id.',
                ]);
        }

        if (!isset($this->eventDispatcher)) {
            return $resource
                ->setStatusCode(HttpStatus::ServiceUnavailable->value)
                ->withData([
                    'ok' => false,
                    'error' => 'Event dispatcher is unavailable.',
                ]);
        }

        $startedAt = microtime(true);
        $run = $this->showcaseService->createRun($mode);
        $event = match ($mode) {
            'sync' => new DemoExecutionShowcaseSyncRequested(),
            'async' => new DemoExecutionShowcaseAsyncRequested(),
            'queued' => new DemoExecutionShowcaseQueuedRequested(),
            default => null,
        };
        if ($event === null) {
            return $resource
                ->setStatusCode(HttpStatus::UnprocessableEntity->value)
                ->withData([
                    'ok' => false,
                    'error' => 'Unsupported mode.',
                ]);
        }

        $event->setRunId($run->getId());
        $event->setSessionId($sessionId);
        $event->setRequestedAt((new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(DATE_ATOM));

        $this->eventDispatcher->dispatch($event);
        $dispatchMs = (int) round((microtime(true) - $startedAt) * 1000);
        $modeMeta = $this->showcaseService->getModeMeta($mode);

        return $resource->withData([
            'ok' => true,
            'mode' => $mode,
            'modeLabel' => $modeMeta['label'] ?? strtoupper($mode),
            'executionLabel' => $modeMeta['execution'] ?? 'Unknown',
            'responseSummary' => match ($mode) {
                'sync' => 'HTTP 200 returned only after the backend listener finished inside the same request.',
                'async' => 'HTTP 200 returned first; the backend listener will finish later and prove it over SSE.',
                'queued' => 'HTTP 200 confirms the queue ticket was published. Worker completion comes later over SSE.',
                default => 'Launch accepted.',
            },
            'runId' => $run->getId(),
            'dispatchMs' => $dispatchMs,
            'status' => $run->getStatus(),
            'message' => match ($mode) {
                'sync' => 'The HTTP response waited for the sync listener to finish.',
                'async' => 'The response is already back; the deferred listener will keep working over SSE.',
                'queued' => 'The event was queued. The worker will confirm progress when it picks the ticket up.',
                default => 'Launch accepted.',
            },
            'queueCommand' => 'bin/semitexa queue:work nats async',
        ]);
    }
}
