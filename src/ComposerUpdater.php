<?php

namespace MartinsR\ComposerConstraintUpdater;

use Safe\Exceptions\ExecException;

use function Safe\shell_exec;

class ComposerUpdater
{
    public function updateComposer(): string
    {
        try {
            $output = shell_exec('composer update');
        } catch (ExecException $e) {
            $output = 'Shell exec didn\'t finish correctly.';
        }

        return $output;
    }
}
