<?php

declare(strict_types=1);

namespace App\Console\Command;

use Semitexa\Core\Attribute\AsCommand;
use Semitexa\Llm\Attribute\AsAiSkill;
use Semitexa\Llm\Policy\AiConfirmationMode;
use Semitexa\Llm\Policy\AiRiskLevel;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'cache:clear', description: 'Clear stale application cache')]
#[AsAiSkill(
    summary: 'Clear stale cache after template or configuration changes.',
    useWhen: 'User asks to flush cache or fix stale rendered output.',
    avoidWhen: 'User asks to rebuild containers or restart Docker.',
    riskLevel: AiRiskLevel::Medium,
    confirmation: AiConfirmationMode::Always,
    argumentPolicy: 'allowlisted',
    exposeArguments: ['twig'],
)]
final class CacheClearCommand extends Command
{
}
