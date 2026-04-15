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
    private const COMPOSER_FILE = 'composer.json';
    private const COMPOSER_EXTRA_PROJECT_DIR_KEY = 'git-templates-project-dir';
    private const COMPOSER_EXTRA_LOCALE_KEY = 'git-templates-locale';
    private const COMPOSER_EXTRA_REPOSITORY_TYPE_KEY = 'git-templates-repository-type';
    private const PROJECT_DIR_ENV = 'PROJECT_DIR';
    private const REPOSITORY_TYPE_ENV = 'REPOSITORY_TYPE';

    private const AVAILABLE_REPOSITORY_TYPES = ['gitlab', 'github', 'gitbucket'];
    private const DEFAULT_REPOSITORY_TYPE = 'gitlab';
    private const TEMPLATES_DIR_BY_REPOSITORY_TYPE = [
        'gitlab' => '.gitlab/issue_templates',
        'github' => '.github/ISSUE_TEMPLATE',
        'gitbucket' => '.gitbucket/issue_templates',
    ];

    private const AVAILABLE_LOCALES = ['fr_FR', 'en_US'];
    private const DEFAULT_LOCALE = 'en_US';

    protected function configure(): void
    {
        $this
            ->addOption(
                'project-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Target project root path. Defaults to composer extra git-templates-project-dir, then PROJECT_DIR env variable.',
            )
            ->addOption(
                'locale',
                'l',
                InputOption::VALUE_REQUIRED,
                'Locale to use (e.g. fr_FR, en_US). Defaults to composer extra git-templates-locale, then LANGUAGE env variable.',
            )
            ->addOption(
                'repository-type',
                'r',
                InputOption::VALUE_REQUIRED,
                'Repository type to use (gitlab, github, gitbucket). Defaults to composer extra git-templates-repository-type, then REPOSITORY_TYPE env variable.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectDirFromComposerExtra = $this->resolveProjectDirFromComposerExtra(getcwd());
        $projectDir = $this->resolveProjectDir($input->getOption('project-dir'), $projectDirFromComposerExtra);

        $repositoryTypeFromComposerExtra = $this->resolveRepositoryTypeFromComposerExtra($projectDir);
        $repositoryType = $this->resolveRepositoryType(
            $input->getOption('repository-type'),
            $repositoryTypeFromComposerExtra,
        );

        $templatesDir = self::TEMPLATES_DIR_BY_REPOSITORY_TYPE[$repositoryType];
        $targetDir = $projectDir . '/' . $templatesDir;

        $localeFromComposerExtra = $this->resolveLocaleFromComposerExtra($projectDir);
        $locale = $this->resolveLocale($input->getOption('locale'), $localeFromComposerExtra);
        $resourcesDir = dirname(__DIR__, 2) . '/resources/templates/' . $locale;

        $io->text(sprintf('<info>✓</info> Project dir: %s', $projectDir));
        $io->text(sprintf('<info>✓</info> Repository type: %s', $repositoryType));
        $io->text(sprintf('<info>✓</info> Locale: %s', $locale));

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
            $io->text(sprintf('<info>✓</info> Directory created: %s', $templatesDir));
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

    private function resolveProjectDir(?string $option, ?string $composerExtraProjectDir): string
    {
        $projectDir = $option
            ?? $composerExtraProjectDir
            ?? getenv(self::PROJECT_DIR_ENV)
            ?: getcwd();

        if (!is_string($projectDir) || $projectDir === '') {
            return getcwd();
        }

        return rtrim($projectDir, '/');
    }

    private function resolveProjectDirFromComposerExtra(string $projectDir): ?string
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

        $projectDir = $extra[self::COMPOSER_EXTRA_PROJECT_DIR_KEY] ?? null;

        if (!is_string($projectDir) || $projectDir === '') {
            return null;
        }

        return $projectDir;
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

    private function resolveRepositoryType(?string $option, ?string $composerExtraRepositoryType): string
    {
        $repositoryType = $option
            ?? $composerExtraRepositoryType
            ?? getenv(self::REPOSITORY_TYPE_ENV)
            ?: self::DEFAULT_REPOSITORY_TYPE;

        return $this->normalizeRepositoryType($repositoryType);
    }

    private function resolveRepositoryTypeFromComposerExtra(string $projectDir): ?string
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

        $repositoryType = $extra[self::COMPOSER_EXTRA_REPOSITORY_TYPE_KEY] ?? null;

        if (!is_string($repositoryType) || $repositoryType === '') {
            return null;
        }

        return $repositoryType;
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

    private function normalizeRepositoryType(string $repositoryType): string
    {
        $normalizedRepositoryType = strtolower(trim($repositoryType));

        if (in_array($normalizedRepositoryType, self::AVAILABLE_REPOSITORY_TYPES, true)) {
            return $normalizedRepositoryType;
        }

        return self::DEFAULT_REPOSITORY_TYPE;
    }
}
