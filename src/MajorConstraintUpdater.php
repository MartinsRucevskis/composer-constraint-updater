<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MajorConstraintUpdater extends BaseCommand
{

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
                    'Composer json file location'
                ),
                new InputOption(
                    'composer-lock',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Composer lock file location'
                ),
                new InputOption(
                    'constraint',
                    null,
                    InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                    'Package/packages to require with a version constraint, e.g. foo/bar:1.0.0 or foo/bar=1.0.0'
                ),
            ])
            ->setHelp(<<<EOT
            The <info>major-update</info> command updates your composer.json file such that other packages are updated
            to the newest versions without a need to resolve conflicts manually.  For example, if you’re upgrading package/package from version 9 to 10 and there are conflicts with other packages,
            you can use major-update -C=composer-path --constraint=package/package:^9.0.0. This command will automatically resolve the conflicts in composer.json, saving you the trouble of doing it manually
            EOT
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerPath = $input->getOption('composer-json') ?? Factory::getComposerFile();
        $composerLock = $input->getOption('composer-lock') ?? Factory::getLockFile($composerPath);

        $composerFileContents = file_get_contents($composerPath) ?: throw new \Exception('Couldn\'t open the composer json from ' . $composerPath);

        $constraints = [];

        foreach ($input->getOption('constraint') as $option) {
            $input = explode(':', $option);
            $constraints[$input[0]] = $input[1];
        }

        $output->writeln('Building composer.json for update');
        file_put_contents($composerPath, $this->replaceVersions($composerFileContents, $constraints));

        $output->writeln('Launching composer update');
        $this->updateComposer();

        $output->writeln('Rebuilding composer.json from lock');
        file_put_contents($composerPath, (new ComposerJsonFromLockBuilder($composerPath, $composerLock))->versionsFromLock());
        
        $output->writeln('Composer.json has been successfully updated!');
        
        return 1;
    }

    private function updateComposer(): void
    {
        $update = shell_exec('composer update');

        echo $update;
    }

    public
    function replaceVersions(string $composerContents, array $packageConstraints): string
    {
        $requirements = ['require', 'require-dev'];
        foreach ($requirements as $requirement) {
            preg_match('/"' . preg_quote($requirement) . '"\s*:\s*\{([\s|\S]+?)\}/m', $composerContents, $matches);
            $packages = preg_replace('/:\s*"(.*)+?"/m', ': "*"', $matches[1]);
            foreach ($packageConstraints as $constraint => $version) {
                $packages = preg_replace(
                    '@"' . preg_quote($constraint) . '"\s*:\s*"\*"@m',
                    '"' . preg_quote($constraint) . '": "' . $version . '"',
                    $packages
                );
            }
            $composerContents = preg_replace('/"' . preg_quote($requirement) . '"\s*:\s*\{([\s|\S]+?)\}/m', '"' . $requirement . '": {' . $packages . '}', $composerContents);
        }

        return $composerContents;
    }
}