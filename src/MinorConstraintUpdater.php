<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MinorConstraintUpdater extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('minor-update')
            ->setDescription('Will run composer update and replace versions in your composer.json file to the ones inside lock file')
            ->setDefinition([
                new InputOption(
                    'composer-json',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Composer json file location'
                ),
                new InputOption(
                    'composer-lock',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Composer lock file location'
                ),
            ])
            ->setHelp(<<<EOT
            The <info>minor-update</info> command executes a composer update, which updates your composer.json
            file to reflect the actual versions of all packages.
            This means it will update the versions of your packages to the latest minor versions, while respecting the version constraints defined in your composer.json file.
            EOT
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerPath = $input->getOption('composer-json') ?? Factory::getComposerFile();
        $composerLock = $input->getOption('composer-lock') ?? Factory::getLockFile($composerPath);

        $output->writeln('Launching composer update');
        $this->updateComposer();

        $output->writeln('Rebuilding composer.json from lock file');
        file_put_contents($composerPath, (new ComposerJsonFromLockBuilder($composerPath, $composerLock))->versionsFromLock());

        $output->writeln('Composer.json has been successfully updated!');
        return 1;
    }

    private function updateComposer(): void
    {
        $update = shell_exec('composer update');

        echo $update;
    }
}