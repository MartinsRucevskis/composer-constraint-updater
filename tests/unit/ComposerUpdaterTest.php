<?php

use MartinsR\ComposerConstraintUpdater\ConstraintUpdaterInitCommand;
use PHPUnit\Framework\TestCase;

class ComposerUpdaterTest extends TestCase
{
    public function testComposerJsonUpdater(): void{
        (new ConstraintUpdaterInitCommand())->replaceVersions(file_get_contents('C:\Users\martins.rucevskis\projects\ComposerConstraintUpdater\tests\resources\composerjson.txt'), ['laravel/framework' => '9.0.0']);
    }
    
    public function testComposerJsonFromLockFile(): void{
        (new ConstraintUpdaterInitCommand())->versionsFromLock('C:\Users\martins.rucevskis\projects\ComposerConstraintUpdater\tests\resources\composer.json.txt');
    }
}