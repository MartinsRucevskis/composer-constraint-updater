<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Composer;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class ComposerPlugin implements PluginInterface, Capable
{
    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io) {}


    /**
     * {@inheritdoc}
     */
    public function getCapabilities(): array
    {
        return [
            ComposerCommandProvider::class => CommandProvider::class,
        ];
    }

    public function deactivate(Composer $composer, IOInterface $io) {}

    public function uninstall(Composer $composer, IOInterface $io) {}
}
