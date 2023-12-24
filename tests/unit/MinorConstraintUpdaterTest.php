<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use MartinsR\ComposerConstraintUpdater\ComposerUpdater;
use MartinsR\ComposerConstraintUpdater\Input;
use MartinsR\ComposerConstraintUpdater\MajorConstraintUpdater;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class MinorConstraintUpdaterTest extends UnitTestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    #[Test]
    public function MajorComposerUpdater(): void
    {
        $inputMock = $this->createMock(Input::class);
        $inputMock->method('composerJson')->willReturn($this->composerJson());
        $inputMock->method('inputConstraints')->willReturn(['laravel/framework:^9.0', 'php:^8.1']);
        $composerUpdateMock = $this->createMock(ComposerUpdater::class);
        $composerUpdateMock->expects(self::once())->method('updateComposer');
        $output = $this->createMock(OutputInterface::class);
        (new MajorConstraintUpdater())->executeUpdate($inputMock, $output, $composerUpdateMock);
        $this->assertComposerJsonContentsEqual($this->resourcePath('expected/composerJsonFromLock.txt'));
    }
}
