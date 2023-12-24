<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use Composer\Console\Input\InputOption;
use MartinsR\ComposerConstraintUpdater\Input;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption as InputOptionAlias;

/**
 * @internal
 */
class InputTest extends UnitTestCase
{
    /**
     * @param array<mixed> $invalidConstraints
     *
     * @throws Exception
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

    /**
     * @param array<mixed> $data
     */
    private function inputForConstraint(array $data): ArrayInput
    {
        return new ArrayInput([], new InputDefinition([
            $this->inputOption('composer-json', $this->resourcePath('composerJson.txt')),
            $this->inputOption('composer-json', $this->resourcePath('composerJson.txt')),
            $this->inputOption('constraint', $data),
        ]));
    }

    private function inputOption(string $name, mixed $data): InputOption
    {
        return new InputOption($name, mode: InputOptionAlias::VALUE_OPTIONAL, default: $data);
    }
}
