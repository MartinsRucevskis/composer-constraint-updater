<?php

namespace MartinsR\ComposerConstraintUpdater;

use MartinsR\ComposerConstraintUpdater\ConstraintUpdater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Safe\shell_exec;

class MinorConstraintUpdater implements ConstraintUpdater
{

    public function executeUpdate(Input $input, OutputInterface $output, ComposerUpdater $composerUpdater)
    {
        $output->writeln('Launching composer update');
        $composerJson = $input->composerJson();
        $composerUpdater->updateComposer();

        $output->writeln('Rebuilding composer.json from lock file');
        $composerJson->rebuildFromLock();

        $output->writeln('Composer.json has been successfully updated!');
    }
}