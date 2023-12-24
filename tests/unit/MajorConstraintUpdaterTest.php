<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use MartinsR\ComposerConstraintUpdater\ComposerUpdater;
use MartinsR\ComposerConstraintUpdater\Input;
use MartinsR\ComposerConstraintUpdater\MinorConstraintUpdater;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use Safe\Exceptions\ExecException;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class MajorConstraintUpdaterTest extends UnitTestCase
{
    /**
     * @throws FilesystemException
     * @throws Exception
     * @throws ExecException
     * @throws JsonException
     * @throws \Exception
     */
    #[Test]
    public function MinorComposerUpdater(): void
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
