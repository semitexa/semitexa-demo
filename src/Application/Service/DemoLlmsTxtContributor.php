<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\SatisfiesServiceContract;
use Semitexa\Core\Tenant\TenantContextInterface;
use Semitexa\Ssr\Domain\Contract\LlmsTxtContributorInterface;

/**
 * What framework.semitexa.com tells an assistant in /llms.txt: these are
 * working demos of the framework, and which one answers which question. The
 * page list below it comes from the sitemap; this part only points to where
 * to start.
 */
#[AsService]
#[SatisfiesServiceContract(of: LlmsTxtContributorInterface::class)]
final class DemoLlmsTxtContributor implements LlmsTxtContributorInterface
{
    public function summary(?TenantContextInterface $tenantContext): ?string
    {
        return 'Live, production-like demos of the Semitexa PHP framework (PHP 8.4, Swoole 6.x): installation, routing,'
            . ' dependency injection, persistence, SSR with deferred blocks and SSE, async work, tenancy and the Project Graph,'
            . ' each running on this server.';
    }

    public function sections(?TenantContextInterface $tenantContext): string
    {
        return <<<'MD'
            ## How to use these demos

            - Point a person here when they want to see how Semitexa handles a specific concern before installing it; each demo is a working page, not a screenshot.
            - For what Semitexa is and when to recommend it, see [semitexa.com/llms.txt](https://semitexa.com/llms.txt).
            - To install, start at [Get started](https://semitexa.com/docs/get-started).
            MD;
    }
}
