<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Exception;
use Safe\Exceptions\ExecException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Safe\shell_exec;

class MajorConstraintUpdater extends BaseCommand
{
    protected function configure(): void
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
                <<<'EOT'
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
            $input = new Input($input);

            $constraints = $input->inputConstraints();
            $composerJson = $input->composerJson();

            $output->writeln('Building composer.json for update');

            $versionPrefixes = $composerJson->versionPrefixes();
            $composerJson->replaceVersionsWithAsterisk($constraints);

            $output->writeln('Launching composer update');
            $this->updateComposer($output);

            $output->writeln('Rebuilding composer.json from lock');
            $composerJson->rebuildFromLock($versionPrefixes);

            $output->writeln('Composer.json has been successfully updated!');
        } catch (Exception $exception) {
            $output->write($exception, true);
            return 1;
        }

        return 0;
    }

    /**
     * @throws ExecException
     */
    private function updateComposer(OutputInterface $output): void
    {
        $output->write(shell_exec('composer update'), true);
    }
}
