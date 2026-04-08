<?php

declare(strict_types=1);

namespace App\Console\Command;

use Semitexa\Core\Attribute\AsCommand;
use Semitexa\Llm\Attribute\AsAiSkill;
use Semitexa\Llm\Policy\AiConfirmationMode;
use Semitexa\Llm\Policy\AiRiskLevel;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'seed:demo-data', description: 'Seed demo data for local environments')]
#[AsAiSkill(
    allowed: 'env::AI_ENABLE_SEED_SKILL::false',
    summary: 'Seed local demo data when the project explicitly allows this skill.',
    useWhen: 'User wants to seed local demo fixtures in a safe non-production context.',
    avoidWhen: 'Production data is involved or the env flag is not enabled.',
    riskLevel: AiRiskLevel::High,
    confirmation: AiConfirmationMode::Always,
    argumentPolicy: 'allowlisted',
    exposeArguments: ['tenant', 'force'],
)]
final class SeedDemoDataCommand extends Command
{
}
