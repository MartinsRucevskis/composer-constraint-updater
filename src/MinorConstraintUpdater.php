<?php

namespace MartinsR\ComposerConstraintUpdater;

use Exception;
use Safe\Exceptions\ExecException;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\Console\Output\OutputInterface;

class MinorConstraintUpdater implements ConstraintUpdater
{
    /**
     * @throws FilesystemException
     * @throws ExecException
     * @throws Exception
     */
    public function executeUpdate(Input $input, OutputInterface $output, ComposerUpdater $composerUpdater): void
    {
        $output->writeln('Launching composer update');
        $composerJson = $input->composerJson();
        $originalPrefixes = $composerJson->versionPrefixes();
        $composerUpdater->updateComposer();

        $output->writeln('Rebuilding composer.json from lock file');
        $composerJson->rebuildFromLock($originalPrefixes);

        $output->writeln('Composer.json has been successfully updated!');
    }
}
