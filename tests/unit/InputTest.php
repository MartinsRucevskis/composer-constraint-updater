<?php

namespace MartinsR\ComposerConstraintUpdater\Tests\Unit;

use Composer\Console\Input\InputOption;
use MartinsR\ComposerConstraintUpdater\Input;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * @internal
 */
class InputTest extends UnitTestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testMinorComposerUpdater(): void
    {
        $input = $this->inputForConstraint(['somePackage=^2']);
        $this->expectExceptionMessage('Wrong input constraint, should be as package:version');
        (new Input($input))->inputConstraints();
    }

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
        return new InputOption($name, mode: InputOption::VALUE_OPTIONAL, default: $data);
    }
}
