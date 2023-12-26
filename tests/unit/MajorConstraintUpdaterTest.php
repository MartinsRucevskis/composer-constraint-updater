<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use MartinsR\ComposerConstraintUpdater\ComposerUpdater;
use MartinsR\ComposerConstraintUpdater\Input;
use MartinsR\ComposerConstraintUpdater\MajorConstraintUpdater;
use MartinsR\ComposerConstraintUpdater\MajorConstraintUpdaterCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class MajorConstraintUpdaterTest extends UnitTestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    #[Test]
    public function whenMajorComposerUpdateThenRebuildComposerJsonCorrectly(): void
    {
        $input = $this->createMock(Input::class);
        $input->method('composerJson')->willReturn($this->composerJson());

        $composerUpdateMock = $this->createMock(ComposerUpdater::class);
        $composerUpdateMock->expects(self::once())->method('updateComposer');

        $output = $this->createMock(OutputInterface::class);

        (new MajorConstraintUpdater())->executeUpdate($input, $output, $composerUpdateMock);

        $this->assertComposerJsonContentsEqual($this->resourcePath('expected/composerJsonFromLock.txt'));
    }

    #[Test]
    #[WithoutErrorHandler]
    public function givenWrongInputWhenLaunchMajorUpdateCommandThenReturn1(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $input = new ArrayInput(['--composer-json' => 'nonExistentPath']);

        $this->assertEquals(1, (new MajorConstraintUpdaterCommand())->run($input, $output));
    }
}
