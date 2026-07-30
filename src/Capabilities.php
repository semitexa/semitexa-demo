<?php

declare(strict_types=1);

namespace Semitexa\Demo;

use Semitexa\Core\Attribute\Capability;

/**
 * What this package offers, for the capability catalog.
 *
 * The package ships no attributes of its own, so there is nothing for a
 * mechanism-level declaration to hang on — and without this the package is
 * invisible to anyone whose project has not installed it, which is precisely
 * the audience worth telling. The convention is one `Capabilities` class per
 * package: a definite place to look, and a definite place for a guard to check.
 *
 * Nothing reads this at runtime.
 */
#[Capability(
    id: 'demo.showcase',
    summary: 'The official showcase application: framework features as running pages, next to the source that produces them.',
    useWhen: 'A feature has to be seen working before it is adopted, or a convention needs a reference implementation to copy from.',
    avoidWhen: 'A production deployment. It is a teaching surface, with routes and fixtures nobody should ship to end users.',
    replaces: [
        'a scratch module written to try a feature out and then left in the project',
        'reading a package test suite to work out how the feature is meant to be assembled',
    ],
)]
final class Capabilities
{
}
