<?php

use MartinsR\ComposerConstraintUpdater\ComposerJson;
use MartinsR\ComposerConstraintUpdater\ComposerJsonFromLockBuilder;
use MartinsR\ComposerConstraintUpdater\FileOpener;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ComposerUpdaterTest extends TestCase
{
    use FileOpener;

    #[Test]
    public function buildComposerJsonForUpdate(): void
    {
        $composerJsonForMajorUpdate = (new ComposerJson($this->resourcePath('composerJson.txt'), $this->resourcePath('composerLock.txt'))
        )->replaceVersions(['laravel/framework' => '^9.0', 'php' => '^8.1']);
        $this->assertEquals(
            $this->fileContents($this->resourcePath('composerJsonForMajorUpdate.txt')),
            $composerJsonForMajorUpdate
        );
    }

    #[Test]
    public function rebuildComposerJsonFileFromLock(): void
    {
        $composerJsonFromLock = (new ComposerJson($this->resourcePath('composerJson.txt'), $this->resourcePath('composerLock.txt'))
        )->rebuildFromLock();
        $this->assertEquals(
            $this->fileContents($this->resourcePath('composerJsonFromLock.txt')),
            $composerJsonFromLock
        );
    }

    private function resourcePath(string $resourceName): string
    {
        return dirname(__DIR__) . '/resources/' . $resourceName;
    }
}
