<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use Exception;
use PHPUnit\Framework\Attributes\Test;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;

/**
 * @internal
 */
class ComposerUpdaterTest extends UnitTestCase
{
    /**
     * @throws FilesystemException
     * @throws JsonException
     * @throws Exception
     */
    #[Test]
    public function buildComposerJsonForUpdate(): void
    {
        $composerJson = $this->composerJson();

        $composerJson->replaceVersionsWithAsterisk(['laravel/framework' => '^9.0', 'php' => '^8.1']);
        $this->assertComposerJsonContentsEqual($this->resourcePath('expected/composerJsonForMajorUpdate.txt'));
    }

    /**
     * @throws FilesystemException
     * @throws Exception
     */
    #[Test]
    public function rebuildComposerJsonFileFromLock(): void
    {
        $composerJson = $this->composerJson();
        $composerJson->rebuildFromLock($composerJson->versionPrefixes());

        $this->assertComposerJsonContentsEqual($this->resourcePath('expected/composerJsonFromLock.txt'));
    }
}
