<?php

namespace MartinsR\ComposerConstraintUpdater;

use Symfony\Component\Console\Output\OutputInterface;

interface ConstraintUpdater
{
    public function executeUpdate(Input $input, OutputInterface $output, ComposerUpdater $composerUpdater);
}
