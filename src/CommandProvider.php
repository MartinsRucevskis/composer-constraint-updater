<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

class CommandProvider implements CommandProviderCapability
{
    public function getCommands(): array
    {
        return [
            new MajorConstraintUpdaterCommand(),
            new MinorConstraintUpdaterCommand(),
        ];
    }
}
