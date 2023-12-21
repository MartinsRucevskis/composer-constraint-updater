<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function Safe\file_put_contents;

class MajorConstraintUpdater extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('major-update')
            ->setDescription(
                'Update your composer.json file to newest possible packages,
                          given constraints(e.g given constraint laravel/laravel 9,
                          updated all the other packages such that there aren\'t conflicts'
            )
            ->setDefinition([
                new InputOption(
                    name: 'composer-json',
                    mode: InputOption::VALUE_OPTIONAL,
                    description: 'Composer json file location',
                    default: Factory::getComposerFile()
                ),
                new InputOption(
                    name: 'composer-lock',
                    mode: InputOption::VALUE_OPTIONAL,
                    description: 'Composer lock file location',
                    default: Factory::getLockFile(Factory::getComposerFile())
                ),
                new InputOption(
                    name: 'constraint',
                    mode: InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    description: 'Package/packages to require with a version constraint, e.g. foo/bar:1.0.0 or foo/bar=1.0.0'
                ),
            ])
            ->setHelp(
                <<<EOT
                    The <info>major-update</info> command updates your composer.json file such that other packages are updated
                    to the newest versions without a need to resolve conflicts manually.  For example, if you’re upgrading package/package from version 9 to 10 and there are conflicts with other packages,
                    you can use major-update --constraint=php:^8.1 --constraint=package/package:^10.0.0. This command will automatically resolve the conflicts in composer.json, saving you the trouble of doing it manually
                    EOT
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $composerPath = $input->getOption('composer-json');
            $composerLock = $input->getOption('composer-lock');

            $constraints = $this->inputConstraints($input);
            $composerJson = new ComposerJson($composerPath, $composerLock);
            $output->writeln('Building composer.json for update');
            file_put_contents($composerPath, $composerJson->replaceVersions($constraints));

            $output->writeln('Launching composer update');
            $this->updateComposer();

            $output->writeln('Rebuilding composer.json from lock');
            $composerJson->rebuildFromLock();

            $output->writeln('Composer.json has been successfully updated!');
        } catch (Exception $exception){
            echo $exception;
            return 1;
        }

        return 0;
    }

    private function updateComposer(): void
    {
        $update = shell_exec('composer update');

        echo $update;
    }

    /**
     * @throws Exception
     */
    private function inputConstraints(InputInterface $input): array{
        $constraints = [];
        foreach ($input->getOption('constraint') as $option) {
            $input = explode(':', $option);
            if(!isset($input[0], $input[1])) {
                throw new Exception('Wrong input constraint, should be as package:version');
            }
            $constraints[$input[0]] = $input[1];
        }
        
        return $constraints;
    }
}
