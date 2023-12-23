<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use Exception;
use MartinsR\ComposerConstraintUpdater\ComposerJson;
use PHPUnit\Framework\TestCase;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;

use function Safe\file_get_contents;
use function Safe\json_decode;

/**
 * @internal
 */
class UnitTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        copy($this->resourcePath('original/originalComposerJson.txt'), $this->resourcePath('composerJson.txt'));
        copy($this->resourcePath('original/originalComposerLock.txt'), $this->resourcePath('composerLock.txt'));
    }

    protected function resourcePath(string $resourceName): string
    {
        return dirname(__DIR__) . '/resources/' . $resourceName;
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    protected function assertComposerJsonContentsEqual(string $expectedComposerJsonPath): void
    {
        $this->assertEquals(
            json_decode(file_get_contents($expectedComposerJsonPath)),
            json_decode(file_get_contents($this->resourcePath('composerJson.txt')))
        );
    }

    /**
     * @throws Exception
     */
    protected function composerJson(): ComposerJson
    {
        return new ComposerJson($this->resourcePath('composerJson.txt'), $this->resourcePath('composerLock.txt'));
    }
}
