<?php

declare(strict_types=1);

namespace AgenceNous\GitTemplates;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'installTemplates',
            ScriptEvents::POST_UPDATE_CMD => 'installTemplates',
        ];
    }

    public function installTemplates(Event $event): void
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $binScript = rtrim($vendorDir, '/') . '/agencenous/git-templates/bin/git-templates';

        if (!file_exists($binScript)) {
            return;
        }

        $event->getIO()->write('<info>agencenous/git-templates:</info> Installing GitLab issue templates...');

        $process = new \Symfony\Component\Process\Process(
            ['php', $binScript],
            getcwd(),
        );
        $process->run(function (string $type, string $buffer) use ($event): void {
            $event->getIO()->write($buffer, false);
        });
    }
}
