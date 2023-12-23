<?php

namespace MartinsR\ComposerConstraintUpdater;

use Safe\Exceptions\ExecException;
use function Safe\shell_exec;

class ComposerUpdater
{
    /**
     * @throws ExecException
     */
    public function updateComposer(): string
    {
       return shell_exec('composer updatesa');
   }
}
