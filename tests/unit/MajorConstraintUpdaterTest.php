<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Console\Input\InputOption;
use Composer\Script\Event;
use phpmock\MockBuilder;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MajorConstraintUpdaterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        copy($this->resourcePath('original/originalComposerJson.txt'), $this->resourcePath('composerJson.txt'));
        copy($this->resourcePath('original/originalComposerLock.txt'), $this->resourcePath('composerLock.txt'));
    }
    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    public function testMajorComposerUpdater()
    {
        $inputMock = $this->createMock(Input::class);
        $inputMock->method('composerJson')->willReturn(
            new ComposerJson($this->resourcePath('composerJson.txt'), $this->resourcePath('composerLock.txt'))
        );
        $inputMock->method('inputConstraints')->willReturn(['laravel/framework:^9.0', 'php:^8.1']);
        $composerUpdateMock = $this->createMock(ComposerUpdater::class);
        $composerUpdateMock->expects(self::once())->method('updateComposer');
        $output = $this->createMock(OutputInterface::class);
        (new MajorConstraintUpdater())->executeUpdate($inputMock, $output, $composerUpdateMock);
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


