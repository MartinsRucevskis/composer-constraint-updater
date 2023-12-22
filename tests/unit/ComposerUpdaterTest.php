<?php

use MartinsR\ComposerConstraintUpdater\ComposerJson;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Safe\Exceptions\FilesystemException;

use function Safe\copy;
use function Safe\file_get_contents;
use function Safe\json_decode;

/**
 * @internal
 */
class ComposerUpdaterTest extends TestCase
{
    /**
     * @throws FilesystemException
     */
    protected function setUp(): void
    {
        parent::setUp();
        copy($this->resourcePath('original/originalComposerJson.txt'), $this->resourcePath('composerJson.txt'));
        copy($this->resourcePath('original/originalComposerLock.txt'), $this->resourcePath('composerLock.txt'));
    }

    /**
     * @throws FilesystemException
     * @throws \Safe\Exceptions\JsonException
     * @throws Exception
     */
    #[Test]
    public function buildComposerJsonForUpdate(): void
    {
        $composerJson = new ComposerJson(
            $this->resourcePath('composerJson.txt'),
            $this->resourcePath('composerLock.txt')
        );

        $composerJson->replaceVersionsWithAsterisk(['laravel/framework' => '^9.0', 'php' => '^8.1']);
        $this->assertEquals(
            json_decode(file_get_contents($this->resourcePath('expected/composerJsonForMajorUpdate.txt'))),
            json_decode(file_get_contents($this->resourcePath('composerJson.txt'))),
        );
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    #[Test]
    public function rebuildComposerJsonFileFromLock(): void
    {
        $composerJson = new ComposerJson(
            $this->resourcePath('composerJson.txt'),
            $this->resourcePath('composerLock.txt')
        );
        $composerJson->rebuildFromLock($composerJson->versionPrefixes());

        $this->assertEquals(
            json_decode(file_get_contents($this->resourcePath('expected/composerJsonFromLock.txt'))),
            json_decode(file_get_contents($this->resourcePath('composerJson.txt'))),
        );
    }

    private function resourcePath(string $resourceName): string
    {
        return dirname(__DIR__) . '/resources/' . $resourceName;
    }
}
