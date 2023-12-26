<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use MartinsR\ComposerConstraintUpdater\Input;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * @internal
 */
class InputTest extends UnitTestCase
{
    /**
     * @param array<mixed> $invalidConstraints
     *
     * @throws \Exception
     */
    #[Test]
    #[TestWith([['somePackage=^2']])]
    #[TestWith([['somePackage']])]
    #[TestWith([[2]])]
    #[TestWith([['randomPackage', ':^2']])]
    public function whenWrongFormatInputThenThrowException(array $invalidConstraints): void
    {
        $input = $this->inputForConstraint($invalidConstraints);
        $this->expectExceptionMessage('Wrong input constraint, should be as package:version');
        (new Input($input))->inputConstraints();
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function whenManyConstraintsReturnFormattedArray(): void
    {
        $input = $this->inputForConstraint([
            'testPackage:^9.0.0.22',
            'testPackage2:~900.0',
            'randomPackage:dev-master',
        ]);
        $this->assertEquals([
            'testPackage' => '^9.0.0.22',
            'testPackage2' => '~900.0',
            'randomPackage' => 'dev-master',
        ], (new Input($input))->inputConstraints());
    }
}
