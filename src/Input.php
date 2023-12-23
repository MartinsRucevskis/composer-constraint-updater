<?php

namespace MartinsR\ComposerConstraintUpdater;

use Exception;
use Symfony\Component\Console\Input\InputInterface;

class Input
{
    public function __construct(private readonly InputInterface $input)
    {
    }

    /**
     * @throws Exception
     */
    public function composerJson(): ComposerJson
    {
        return new ComposerJson($this->input->getOption('composer-json'), $this->input->getOption('composer-lock'));
    }

    /**
     * @return array<string, string>
     *
     * @throws Exception
     */
    public function inputConstraints(): array
    {
        $constraints = [];
        foreach ($this->input->getOption('constraint') as $option) {
            $constraint = explode(':', $option);
            if (!isset($constraint[0], $constraint[1])) {
                throw new Exception('Wrong input constraint, should be as package:version');
            }
            $constraints[$constraint[0]] = $constraint[1];
        }

        return $constraints;
    }
}
