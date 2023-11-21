<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

class CommandProvider implements CommandProviderCapability
{
    /**
     * {@inheritdoc}
     */
    public function getCommands(): array
    {
        return [
            new MajorConstraintUpdater(),
            new MinorConstraintUpdater(),
        ];
    }
}
