<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MajorConstraintUpdater extends BaseCommand
{
    use FileOpener;

    protected function configure()
    {
        $this
            ->setName('major-update')
            ->setDescription(
                'Updater your composer json file to newest,
                          but given constraint supported packages(e.g given constaint laravel/laravel 9,
                          updated all the other packages such that there arent conflicts'
            )
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
                new InputOption(
                    'constraint',
                    null,
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'Package/packages to require with a version constraint, e.g. foo/bar:1.0.0 or foo/bar=1.0.0'
                ),
            ])
            ->setHelp(
                <<<EOT
            The <info>major-update</info> command updates your composer.json file such that other packages are updated
            to the newest versions without a need to resolve conflicts manually.  For example, if you’re upgrading package/package from version 9 to 10 and there are conflicts with other packages,
            you can use major-update -C=composer-path --constraint=package/package:^9.0.0. This command will automatically resolve the conflicts in composer.json, saving you the trouble of doing it manually
            EOT
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerPath = $input->getOption('composer-json');
        $composerLock = $input->getOption('composer-lock');

        $constraints = [];

        foreach ($input->getOption('constraint') as $option) {
            $input = explode(':', $option);
            if(!isset($option[1], $option[2])) {
                throw new Exception('Wrong input constraint, should be as package:version');
            }
            $constraints[$input[0]] = $input[1];
        }
        $composerJson = new ComposerJson($composerPath, $composerLock);
        $output->writeln('Building composer.json for update');
        file_put_contents(
            $composerPath,
            $composerJson->replaceVersions($constraints)
        );

        $output->writeln('Launching composer update');
        $this->updateComposer();

        $output->writeln('Rebuilding composer.json from lock');
        file_put_contents(
            $composerPath,
            $composerJson->rebuildFromLock()
        );

        $output->writeln('Composer.json has been successfully updated!');

        return 1;
    }

    private function updateComposer(): void
    {
        $update = shell_exec('composer update');

        echo $update;
    }
}
