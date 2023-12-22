<?php

namespace MartinsR\ComposerConstraintUpdater;

use Exception;

use function Safe\file_get_contents;
use function Safe\json_decode;
use function Safe\json_encode;

class ComposerJsonFromLockBuilder
{
    public function __construct(
        private readonly string $composerJsonPath,
        private readonly string $composerLockPath
    ) {
    }

    /**
     * @param array<string, array<string, string>> $originalVersionPrefixes
     *
     * @throws Exception
     */
    public function versionsFromLock(array $originalVersionPrefixes): string
    {
        $composerLock = json_decode(file_get_contents($this->composerLockPath), true);
        $composerJson = json_decode(file_get_contents($this->composerJsonPath), true);

        $dependencyPrefixes = ['', '-dev'];

        foreach ($dependencyPrefixes as $dependencyPrefix) {
            $dependencyNames = $this->dependencyNames($composerJson, $dependencyPrefix);
            $composerLockPackages = $composerLock['packages' . $dependencyPrefix];
            $usedPackagesFromLock = array_filter($composerLockPackages, function ($package) use ($dependencyNames) {
                return in_array($package['name'], $dependencyNames);
            });
            $packageVersions = array_column($usedPackagesFromLock, 'version', 'name');

            foreach ($dependencyNames as $dependencyName) {
                $composerJson['require' . $dependencyPrefix][$dependencyName] = $originalVersionPrefixes[$dependencyName] . $packageVersions[$dependencyName];
            }
        }

        return json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<string, array<mixed>> $composerJsonContents
     *
     * @return array<int, string>
     */
    private function dependencyNames(array $composerJsonContents, string $dependencyType): array
    {
        $packages = array_filter(array_keys($composerJsonContents['require' . $dependencyType]), function ($package) {
            return str_contains($package, '/');
        });
        return array_values($packages);
    }
}
