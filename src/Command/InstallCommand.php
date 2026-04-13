<?php

declare(strict_types=1);

namespace AgenceNous\GitTemplates\Command;

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
    private const COMPOSER_FILE = 'composer.json';
    private const COMPOSER_EXTRA_LOCALE_KEY = 'git-templates-locale';
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
                'Locale to use (e.g. fr_FR, en_US). Defaults to composer extra git-templates-locale, then LANGUAGE env variable.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectDir = rtrim($input->getOption('project-dir'), '/');
        $targetDir = $projectDir . '/' . self::TEMPLATES_DIR;
        $localeFromComposerExtra = $this->resolveLocaleFromComposerExtra($projectDir);
        $locale = $this->resolveLocale($input->getOption('locale'), $localeFromComposerExtra);
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

    private function resolveLocale(?string $option, ?string $composerExtraLocale): string
    {
        $locale = $option ?? $composerExtraLocale ?? getenv('LANGUAGE') ?: self::DEFAULT_LOCALE;

        return $this->normalizeLocale($locale);
    }

    private function resolveLocaleFromComposerExtra(string $projectDir): ?string
    {
        $composerPath = $projectDir . '/' . self::COMPOSER_FILE;

        if (!is_file($composerPath)) {
            return null;
        }

        $composerContent = file_get_contents($composerPath);

        if (!is_string($composerContent)) {
            return null;
        }

        try {
            $composerData = json_decode($composerContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($composerData)) {
            return null;
        }

        $extra = $composerData['extra'] ?? null;

        if (!is_array($extra)) {
            return null;
        }

        $locale = $extra[self::COMPOSER_EXTRA_LOCALE_KEY] ?? null;

        if (!is_string($locale) || $locale === '') {
            return null;
        }

        return $locale;
    }

    private function normalizeLocale(string $locale): string
    {
        $normalizedLocale = strtok($locale, '.:');

        // Handle values like "fr_FR.UTF-8" or "fr_FR:en"
        if (is_string($normalizedLocale) && in_array($normalizedLocale, self::AVAILABLE_LOCALES, true)) {
            return $normalizedLocale;
        }

        return self::DEFAULT_LOCALE;
    }
}
