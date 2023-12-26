<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use MartinsR\ComposerConstraintUpdater\ComposerUpdater;
use MartinsR\ComposerConstraintUpdater\Input;
use MartinsR\ComposerConstraintUpdater\MinorConstraintUpdater;
use MartinsR\ComposerConstraintUpdater\MinorConstraintUpdaterCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\MockObject\Exception;
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
}
