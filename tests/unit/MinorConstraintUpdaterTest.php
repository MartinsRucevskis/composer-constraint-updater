<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use MartinsR\ComposerConstraintUpdater\ComposerUpdater;
use MartinsR\ComposerConstraintUpdater\Input;
use MartinsR\ComposerConstraintUpdater\MinorConstraintUpdater;
use MartinsR\ComposerConstraintUpdater\MinorConstraintUpdaterCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\MockObject\Exception;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class MinorConstraintUpdaterTest extends UnitTestCase
{
    #[Test]
    public function whenExecuteMinorUpdateThenRebuildComposerJsonCorrectly(): void
    {
        $input = $this->createMock(Input::class);
        $input->method('composerJson')->willReturn($this->composerJson());

        $composerUpdateMock = $this->createMock(ComposerUpdater::class);
        $composerUpdateMock->expects(self::once())->method('updateComposer');

        $output = $this->createMock(OutputInterface::class);

        (new MinorConstraintUpdater())->executeUpdate($input, $output, $composerUpdateMock);

        $this->assertComposerJsonContentsEqual($this->resourcePath('expected/composerJsonFromLock.txt'));
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    #[Test]
    #[WithoutErrorHandler]
    public function givenWrongInputWhenLaunchMinorUpdateCommandThenReturn1(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $input = new ArrayInput(['--composer-json' => 'nonExistentPath']);
        $this->assertEquals(1, (new MinorConstraintUpdaterCommand())->run($input, $output));
    }

    /**
     * @throws ExceptionInterface
     * @throws FilesystemException
     * @throws Exception
     * @throws JsonException
     */
    #[Test]
    #[WithoutErrorHandler]
    public function whenLaunchMinotConstraintUpdaterThenRebuildComposerJsonCorrectly(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $composerUpdater = $this->getMockBuilder(ComposerUpdater::class)->getMock();
        $composerUpdater->expects($this->once())->method('updateComposer');

        $input = new ArrayInput(['--composer-json' => $this->composerJsonPath(), '--composer-lock' => $this->composerLockPath()]);
        (new MinorConstraintUpdaterCommand(composerUpdater: $composerUpdater))->run($input, $output);
        $this->assertComposerJsonContentsEqual($this->resourcePath('expected/composerJsonFromLock.txt'));
    }
}
