<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\SessionPayloadsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SessionPayloadsPayload::class, resource: DemoFeatureResource::class)]
final class SessionPayloadsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(SessionPayloadsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('auth', 'session-payloads') ?? [];

        return $resource
            ->pageTitle('Session Payloads — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'auth',
                'currentSlug' => 'session-payloads',
                'infoWhat' => $explanation['what'] ?? 'Semitexa treats session state as a typed contract, not as an unstructured key-value dump.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('auth')
            ->withSlug('session-payloads')
            ->withTitle('Session Payloads')
            ->withSummary('Semitexa forbids string-key session chaos: session state lives in typed Session Payloads or it does not exist.')
            ->withEntryLine('Session state should be explicit, typed, and reviewable — not a bag of magic keys spread across handlers.')
            ->withHighlights(['#[SessionSegment]', 'typed session contract', 'no string keys', 'SessionInterface::getPayload()'])
            ->withLearnMoreLabel('See the session contract →')
            ->withDeepDiveLabel('Why string-key sessions rot →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/session-payloads.html.twig', [
                'painPoints' => [
                    'String keys like current_user, auth_user, or user_id drift across handlers, middleware, and listeners until nobody knows the real contract anymore.',
                    'Every caller starts duplicating has/get/null checks because the session shape is implicit and fragile.',
                    'Renaming one key becomes a distributed grep problem instead of a refactor-safe code change.',
                ],
                'signals' => [
                    ['value' => '0', 'label' => 'magic session keys tolerated'],
                    ['value' => '1', 'label' => 'typed session contract'],
                    ['value' => '100%', 'label' => 'refactor-safe session access'],
                ],
                'compare' => [
                    [
                        'variant' => 'warning',
                        'eyebrow' => 'Legacy Session Mess',
                        'title' => 'Handlers guess keys and patch around nulls',
                        'summary' => 'Session reads happen through ad hoc strings, so every caller has to remember both the key names and the failure cases.',
                        'note' => 'The real auth contract lives in tribal knowledge, not in code.',
                    ],
                    [
                        'variant' => 'active',
                        'eyebrow' => 'Typed Session Contract',
                        'title' => 'Session state is one explicit payload',
                        'summary' => 'A dedicated Session Payload owns the shape, and handlers ask for that payload directly through SessionInterface.',
                        'note' => 'Reviewers can see the contract immediately, and refactors stay local.',
                    ],
                ],
                'rules' => [
                    'Session state belongs to a dedicated payload class marked with #[SessionSegment].',
                    'Code reads session state through SessionInterface::getPayload(), not arbitrary string keys.',
                    'Meaningful methods such as requireUserId() or clear() belong on the payload itself.',
                    'If no Session Payload exists for a concern, that concern should not be writing random session keys.',
                ],
            ])
            ->withSourceCode([
                'Legacy Session Access' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Auth/Session/LegacySessionAccess.example.php'),
                'Session Payload' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Auth/Session/BrowserSessionSegment.example.php'),
                'Typed Login Flow' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Auth/Session/LoginHandler.example.php'),
                'Typed Restore Flow' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Auth/Session/SessionAuthHandler.example.php'),
            ])
            ->withExplanation($explanation);
    }
}
