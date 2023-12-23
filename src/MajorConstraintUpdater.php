<?php

namespace MartinsR\ComposerConstraintUpdater;

use MartinsR\ComposerConstraintUpdater\ConstraintUpdater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Safe\shell_exec;

class MajorConstraintUpdater implements ConstraintUpdater
{

    public function executeUpdate(Input $input, OutputInterface $output, ComposerUpdater $composerUpdater)
    {
        $constraints = $input->inputConstraints();
        $composerJson = $input->composerJson();

        $output->writeln('Building composer.json for update');

        $versionPrefixes = $composerJson->versionPrefixes();
        $composerJson->replaceVersionsWithAsterisk($constraints);

        $output->writeln('Launching composer update');
        $output->write($composerUpdater->updateComposer(), true);

        $output->writeln('Rebuilding composer.json from lock');
        $composerJson->rebuildFromLock($versionPrefixes);

        $output->writeln('Composer.json has been successfully updated!');
    }
}