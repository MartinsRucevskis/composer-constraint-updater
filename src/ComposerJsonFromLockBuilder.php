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

        $requirementPrefixes = ['', '-dev'];

        foreach ($requirementPrefixes as $prefix) {
            $packageNames = $this->packageNames($composerJson, $prefix);
            $composerLockPackages = array_merge($composerLock['packages'], $composerLock['packages-dev']);
            $usedPackagesFromLock = array_filter($composerLockPackages, fn ($package): bool => in_array($package['name'], $packageNames));
            $usedPackageVersions = array_column($usedPackagesFromLock, 'version', 'name');

            foreach ($packageNames as $dependencyName) {
                $composerJson['require' . $prefix][$dependencyName] = $originalVersionPrefixes[$dependencyName] . $usedPackageVersions[$dependencyName];
            }
        }

        return json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<string, array<mixed>> $composerJson
     *
     * @return array<int, string>
     */
    private function packageNames(array $composerJson, string $dependencyType): array
    {
        $packages = array_filter(array_keys($composerJson['require' . $dependencyType]), fn ($package): bool => str_contains((string) $package, '/'));
        return array_values($packages);
    }
}
