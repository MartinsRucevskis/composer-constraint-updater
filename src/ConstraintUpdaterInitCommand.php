<?php

namespace MartinsR\ComposerConstraintUpdater;

use App\Shift\ComposerDependencyResolver\ComposerDependency;
use Composer\Command\InitCommand;
use Composer\DependencyResolver\Pool;
use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\Package\BasePackage;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\RepositoryFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ConstraintUpdaterInitCommand extends InitCommand
{

    protected function configure()
    {
        // @codingStandardsIgnoreStart
        $this
            ->setName('constraintupdater-init')
            ->setDescription('Updater your composer json file to newest, but given constraint supported packages(e.g given constaint laravel/laravel 9, updated all the other packages such that there arent conflicts')
            ->setDefinition([
                new InputOption('composer-path', 'C', InputOption::VALUE_REQUIRED, 'Composer json file location'),
                new InputOption('constraint', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Package/packages to require with a version constraint, e.g. foo/bar:1.0.0 or foo/bar=1.0.0'),
            ])
            ->setHelp(<<<EOT
            The <info>constraintupdater-init</info> command creates updates your composer.json file such that other packages are updated
            to the newest versions without a need to resolve conflicts manually. E.g upgrading laravel/laravel from 9-10 there may be conflicts with other packages, but
            by using constraintupdater-init -C=path --constraint=laravel/laravel:9, you dont have to fix the conflicts in composer json manually
            
            EOT
            );
        // @codingStandardsIgnoreEnd
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerPath = $input->getOption('composer-path');
        $composerFileContents = file_get_contents($composerPath);
        $constraints = [];
        foreach ($input->getOption('constrain') as $option) {
            $input = explode(':', $option);
            $constraints[$input[0]] = $input[1];
        }
        file_put_contents($composerPath, $this->replaceVersions($composerFileContents, $constraints));

        $this->updateComposer();

        file_put_contents($composerPath, $this->versionsFromLock($composerPath));
        return 1;
    }

    private function updateComposer(): void
    {
        $process = new Process(['composer', 'update']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        echo $process->getOutput();
    }

    public function versionsFromLock(string $composerPath)
    {
        $composerLockPath = str_replace('composer.json', 'composer.lock', $composerPath);

        $composerLockContents = file_get_contents($composerLockPath);
        $composerJsonContents = file_get_contents($composerPath);
        preg_match('/"require":\s+{([\s\S]+?)}/', $composerJsonContents, $dependencies);
        $dependencies = $this->processDependencies(preg_split('/,/ms', $dependencies[1]));
        foreach ($dependencies as $dependencyName => $version) {
            if ($dependencyName !== 'php') {
                preg_match('#"packages": \[[\s\S]+?"name": "' . preg_quote($dependencyName) . '",\s+"version": "(.+)"#m', $composerLockContents, $match);
                $composerJsonContents = str_replace('"' . $dependencyName . '": "' . $version . '"', '"' . $dependencyName . ': "^' . $match[1] . '"', $composerJsonContents);
            }
        }

        return [];

    }

    public function replaceVersions(string $composerContents, array $packageConstraints): string
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
            $composerContents = preg_replace('/"' . preg_quote($requirement) . '"\s*:\s*\{([\s|\S]+?)\}/m', $packages, $composerContents);
        }

        return $composerContents;
    }

    private function processDependencies(array $dependencies): array
    {
        $composerDependencies = [];
        foreach ($dependencies as &$dependency) {
            $dependency = preg_replace('/\s+/ms', '', $dependency);
            $dependency = explode(':', $dependency);
            $dependency[0] = str_replace('"', '', $dependency[0]);
            $dependency[1] = str_replace('"', '', $dependency[1]);
            $dependency[1] = str_replace('^', '', $dependency[1]);
            $composerDependencies[$dependency[0]] = $dependency[1];
        }

        return $composerDependencies;
    }
}