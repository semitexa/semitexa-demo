<?php

declare(strict_types=1);

namespace Semitexa\Demo\Console\Command;

use Semitexa\Core\Attribute\AsCommand;
use Semitexa\Core\Console\Command\BaseCommand;
use Semitexa\Demo\Application\Service\DemoDataSeeder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'semitexa:demo:seed', description: 'Seed demo data (categories, products, reviews, orders, jobs, AI tasks)')]
class DemoSeedCommand extends BaseCommand
{
    public function __construct(
        private readonly DemoDataSeeder $seeder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('semitexa:demo:seed')
            ->setDescription('Seed demo data (categories, products, reviews, orders, jobs, AI tasks)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Re-seed even if data already exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        try {
            if (!$force && $this->seeder->isSeeded()) {
                $io->note('Demo data already exists. Use --force to re-seed.');
                return Command::SUCCESS;
            }

            $io->section('Seeding demo data...');

            $counts = $this->seeder->seed();

            $rows = [];
            foreach ($counts as $entity => $count) {
                $rows[] = [$entity, $count];
            }

            $io->table(['Entity', 'Count'], $rows);

            $total = array_sum($counts);
            $io->success(sprintf('Demo seed complete: %d records created.', $total));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Demo seed failed: ' . $e->getMessage());
            if ($output->isVerbose()) {
                $io->text($e->getTraceAsString());
            }
            return Command::FAILURE;
        }
    }
}
