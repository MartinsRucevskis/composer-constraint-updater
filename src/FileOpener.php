<?php

namespace MartinsR\ComposerConstraintUpdater;

trait FileOpener
{
    public function fileContents(string $filePath): string
    {
        return file_get_contents($filePath)
            ?: throw new \Exception('Couldn\'t open the file ' . $filePath);
    }
}
