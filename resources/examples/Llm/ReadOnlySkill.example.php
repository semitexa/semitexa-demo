<?php

declare(strict_types=1);

namespace App\Console\Command;

use Semitexa\Core\Attribute\AsCommand;
use Semitexa\Llm\Attribute\AsAiSkill;
use Semitexa\Llm\Policy\AiConfirmationMode;
use Semitexa\Llm\Policy\AiRiskLevel;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'contracts:list', description: 'Inspect DI bindings')]
#[AsAiSkill(
    summary: 'Show active service contract bindings.',
    useWhen: 'User wants to inspect DI wiring or understand which implementation is active.',
    avoidWhen: 'User wants to modify service bindings.',
    riskLevel: AiRiskLevel::Low,
    confirmation: AiConfirmationMode::Never,
    argumentPolicy: 'allowlisted',
    exposeArguments: ['json'],
)]
final class ContractsListCommand extends Command
{
}
