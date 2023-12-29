<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use Composer\Console\Input\InputOption;
use Exception;
use MartinsR\ComposerConstraintUpdater\ComposerJson;
use PHPUnit\Framework\TestCase;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption as InputOptionAlias;

use function Safe\copy;
use function Safe\file_get_contents;
use function Safe\json_decode;

/**
 * @internal
 */
class UnitTestCase extends TestCase
{
    /**
     * @throws FilesystemException
     */
    protected function tearDown(): void
    {
        copy($this->resourcePath('original/originalComposerJson.txt'), $this->resourcePath('composerJson.txt'));
        copy($this->resourcePath('original/originalComposerLock.txt'), $this->resourcePath('composerLock.txt'));
        parent::tearDown();
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
        return new ComposerJson($this->composerJsonPath(), $this->composerLockPath());
    }

    protected function composerJsonPath()
    {
        return $this->resourcePath('composerJson.txt');
    }

    protected function composerLockPath()
    {
        return $this->resourcePath('composerLock.txt');
    }

    /**
     * @param array<mixed> $data
     */
    protected function inputForConstraint(array $data): ArrayInput
    {
        return new ArrayInput([], new InputDefinition([
            $this->inputOption('composer-json', $this->resourcePath('composerJson.txt')),
            $this->inputOption('composer-lock', $this->resourcePath('composerLock.txt')),
            $this->inputOption('constraint', $data),
        ]));
    }

    protected function inputOption(string $name, mixed $data): InputOption
    {
        return new InputOption($name, mode: InputOptionAlias::VALUE_OPTIONAL, default: $data);
    }
}
