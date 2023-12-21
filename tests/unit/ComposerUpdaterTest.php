<?php

use MartinsR\ComposerConstraintUpdater\ComposerJson;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

class ComposerUpdaterTest extends TestCase
{

    #[Test]
    public function buildComposerJsonForUpdate(): void
    {
        $composerJsonForMajorUpdate = (new ComposerJson($this->resourcePath('composerJson.txt'), $this->resourcePath('composerLock.txt'))
        )->replaceVersions(['laravel/framework' => '^9.0', 'php' => '^8.1']);
        $this->assertEquals(
            file_get_contents($this->resourcePath('composerJsonForMajorUpdate.txt')),
            $composerJsonForMajorUpdate
        );
    }

    #[Test]
    public function rebuildComposerJsonFileFromLock(): void
    {
        (new ComposerJson($this->resourcePath('composerJson.txt'), $this->resourcePath('composerLock.txt'))
        )->rebuildFromLock();
        $this->assertEquals(
            file_get_contents($this->resourcePath('composerJsonFromLock.txt')),
            file_get_contents('composerJson.txt')
        );
    }

    private function resourcePath(string $resourceName): string
    {
        return dirname(__DIR__) . '/resources/' . $resourceName;
    }
}
