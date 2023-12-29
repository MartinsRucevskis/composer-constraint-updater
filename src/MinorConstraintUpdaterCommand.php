<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class MinorConstraintUpdaterCommand extends BaseCommand
{
    public function __construct(string $name = null, private ?ComposerUpdater $composerUpdater = null)
    {
        $this->composerUpdater = $composerUpdater ?? new ComposerUpdater();
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('minor-update')
            ->setDescription('Will run composer update and replace versions in your composer.json file to the ones inside lock file')
            ->setDefinition([
                new InputOption(
                    'composer-json',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Composer json file location',
                    Factory::getComposerFile()
                ),
                new InputOption(
                    'composer-lock',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Composer lock file location',
                    Factory::getLockFile(Factory::getComposerFile())
                ),
            ])
            ->setHelp(
                <<<'EOT'
                    The <info>minor-update</info> command executes a composer update, which updates your composer.json
                    file to reflect the actual versions of all packages.
                    This means it will update the versions of your packages to the latest minor versions, while respecting the version constraints defined in your composer.json file.
                    EOT
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $input = new Input($input);
            $composerUpdater = new ComposerUpdater();
            (new MinorConstraintUpdater())->executeUpdate($input, $output, $this->composerUpdater);
        } catch (Throwable $e) {
            $output->write($e, true);
            return 1;
        }
        return 0;
    }
}
