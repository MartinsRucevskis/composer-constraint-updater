<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use MartinsR\ComposerConstraintUpdater\ComposerUpdater;
use MartinsR\ComposerConstraintUpdater\Input;
use MartinsR\ComposerConstraintUpdater\MinorConstraintUpdater;
use PHPUnit\Framework\MockObject\Exception;
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
    public function testMinorComposerUpdater(): void
    {
        $inputMock = $this->createMock(Input::class);
        $inputMock->method('composerJson')->willReturn($this->composerJson());
        $composerUpdateMock = $this->createMock(ComposerUpdater::class);
        $composerUpdateMock->expects(self::once())->method('updateComposer');
        $output = $this->createMock(OutputInterface::class);
        (new MinorConstraintUpdater())->executeUpdate($inputMock, $output, $composerUpdateMock);
        $this->assertComposerJsonContentsEqual($this->resourcePath('expected/composerJsonFromLock.txt'));
    }
}
