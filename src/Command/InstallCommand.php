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
    description: 'Installs or updates GitLab issue templates.',
)]
class InstallCommand extends Command
{
    private const TEMPLATES_DIR = '.gitlab/issue_templates';
    private const AVAILABLE_LOCALES = ['fr_FR', 'en_US'];
    private const DEFAULT_LOCALE = 'en_US';

    protected function configure(): void
    {
        $this
            ->addOption(
                'project-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Target project root path.',
                getcwd(),
            )
            ->addOption(
                'locale',
                'l',
                InputOption::VALUE_REQUIRED,
                'Locale to use (e.g. fr_FR, en_US). Defaults to LANGUAGE env variable.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectDir = rtrim($input->getOption('project-dir'), '/');
        $targetDir = $projectDir . '/' . self::TEMPLATES_DIR;
        $locale = $this->resolveLocale($input->getOption('locale'));
        $resourcesDir = dirname(__DIR__, 2) . '/resources/templates/' . $locale;

        $io->text(sprintf('<info>✓</info> Locale: %s', $locale));

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
            $io->text(sprintf('<info>✓</info> Directory created: %s', self::TEMPLATES_DIR));
        }

        foreach (glob($resourcesDir . '/*.md') as $templatePath) {
            $filename = basename($templatePath);
            copy($templatePath, $targetDir . '/' . $filename);
            $io->text(sprintf('<info>✓</info> %s installed.', $filename));
        }

        $io->newLine();
        $io->success('Templates up to date.');

        return Command::SUCCESS;
    }

    private function resolveLocale(?string $option): string
    {
        $locale = $option ?? getenv('LANGUAGE') ?: self::DEFAULT_LOCALE;

        $locale = strtok($locale, '.:');

        if (in_array($locale, self::AVAILABLE_LOCALES, true)) {
            return $locale;
        }

        return self::DEFAULT_LOCALE;
    }
}
