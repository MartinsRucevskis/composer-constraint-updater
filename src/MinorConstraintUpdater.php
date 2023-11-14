<?php

namespace MartinsR\ComposerConstraintUpdater;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MinorConstraintUpdater extends BaseCommand
{

    protected function configure()
    {
        // @codingStandardsIgnoreStart
        $this
            ->setName('minor-update')
            ->setDescription('Will run composer update and replace versions in your composer.json file to the ones inside lock file')
            ->setDefinition([
                new InputOption('composer-path', 'C', InputOption::VALUE_REQUIRED, 'Composer json file location'),
            ])
            ->setHelp(<<<EOT
            The <info>minor-update</info> command executes a composer update, which updates your composer.json
            file to reflect the actual versions of all packages.
            This means it will update the versions of your packages to the latest minor versions, while respecting the version constraints defined in your composer.json file.
            EOT
            );
        // @codingStandardsIgnoreEnd
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerPath = $input->getOption('composer-path');

        $this->updateComposer();
        file_put_contents($composerPath, $this->versionsFromLock($composerPath));

        return 1;
    }

    private function updateComposer(): void
    {
        $update = shell_exec('composer update');

        echo $update;
    }

    public function versionsFromLock(string $composerPath)
    {
        $composerLockPath = str_replace('composer.json', 'composer.lock', $composerPath);

        $composerLockContents = file_get_contents($composerLockPath);
        $composerJsonContents = file_get_contents($composerPath);

        $types = ['require' => 'packages', 'require-dev' => 'packages-dev'];
        foreach ($types as $typeJson => $typeLock) {
            preg_match('/"' . preg_quote($typeJson) . '":\s+{([\s\S]+?)}/', $composerJsonContents, $dependencies);
            $dependencies = $this->processDependencies(preg_split('/,/ms', $dependencies[1]));
            foreach ($dependencies as $dependencyName => $version) {
                preg_match('#"name": ' . preg_quote($dependencyName) . ',\s+"version": "(.+)"#m', $composerLockContents, $match);
                if (isset($match[1])) {
                    echo  $dependencyName . ': ' . $version;
                    $composerJsonContents = str_replace($dependencyName . ': ' . $version, $dependencyName . ': "^' . $match[1] . '"', $composerJsonContents);
                }
            }
        }

        return $composerJsonContents;
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

    private
    function processDependencies(array $dependencies): array
    {
        $composerDependencies = [];
        foreach ($dependencies as &$dependency) {
            $dependency = preg_replace('/\s+/ms', '', $dependency);
            $dependency = explode(':', $dependency);
            $composerDependencies[$dependency[0]] = $dependency[1];
        }

        return $composerDependencies;
    }
}