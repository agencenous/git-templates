<?php

declare(strict_types=1);

namespace AgenceNous\GitlabTemplates\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'install',
    description: 'Crée ou met à jour les templates d\'issue GitLab.',
)]
class InstallCommand extends Command
{
    private const TEMPLATES_DIR = '.gitlab/issue_templates';

    protected function configure(): void
    {
        $this->addOption(
            'project-dir',
            'd',
            InputOption::VALUE_REQUIRED,
            'Chemin racine du projet cible.',
            getcwd(),
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectDir = rtrim($input->getOption('project-dir'), '/');
        $targetDir = $projectDir . '/' . self::TEMPLATES_DIR;
        $resourcesDir = dirname(__DIR__, 2) . '/resources/templates';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
            $io->text(sprintf('<info>✓</info> Dossier créé : %s', self::TEMPLATES_DIR));
        }

        foreach (glob($resourcesDir . '/*.md') as $templatePath) {
            $filename = basename($templatePath);
            copy($templatePath, $targetDir . '/' . $filename);
            $io->text(sprintf('<info>✓</info> %s installé.', $filename));
        }

        $io->newLine();
        $io->success('Templates à jour.');

        return Command::SUCCESS;
    }
}