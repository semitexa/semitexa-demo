<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/session-payloads',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'auth',
    title: 'Session Payloads',
    slug: 'session-payloads',
    summary: 'Semitexa forbids string-key session chaos: session state lives in typed Session Payloads or it does not exist.',
    order: 1,
    highlights: ['#[SessionSegment]', 'typed session contract', 'no string keys', 'SessionInterface::getPayload()'],
    entryLine: 'Session state should be explicit, typed, and reviewable — not a bag of magic keys spread across handlers.',
    learnMoreLabel: 'See the session contract →',
    deepDiveLabel: 'Why string-key sessions rot →',
)]
final class SessionPayloadsPayload
{
}
