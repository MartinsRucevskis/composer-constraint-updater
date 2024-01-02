<?php

namespace MartinsR\ComposerConstraintUpdater;

use Exception;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Safe\Exceptions\PcreException;

use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\preg_match;

class ComposerJson
{
    /**
     * @throws Exception
     */
    public function __construct(private readonly string $composerJsonPath, private readonly string $composerLockPath)
    {
    }

    /**
     * @return array<mixed>
     *
     * @throws JsonException
     * @throws PcreException
     * @throws FilesystemException
     */
    public function versionPrefixes(): array
    {
        foreach ($this->packages() as $package => $version) {
            if (str_contains((string) $package, '/')) {
                preg_match('#([\^|~|>|=|<]*)#s', (string) $version, $versionPrefix);
                $constraints[$package] = $versionPrefix[1];
            }
        }

        return $constraints ?? [];
    }

    /**
     * @param array<string, string> $packageConstraints
     *
     * @throws JsonException
     * @throws FilesystemException
     */
    public function replaceVersionsWithAsterisk(array $packageConstraints): void
    {
        $composerJson = json_decode(file_get_contents($this->composerJsonPath), true);
        $packagesToUpdate = array_filter($this->packages(), fn ($package) => $this->shouldPackageBeUpdated($package), ARRAY_FILTER_USE_KEY);

        foreach ($packagesToUpdate as $packageToUpdate => $version) {
            isset($composerJson['require'][$packageToUpdate])
                ? $composerJson['require'][$packageToUpdate] = $packageConstraints[$packageToUpdate] ?? '*'
                : $composerJson['require-dev'][$packageToUpdate] = $packageConstraints[$packageToUpdate] ?? '*';
        }

        $this->updateContents(json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param array<string, array<string, string>> $versionPrefixes
     *
     * @throws FilesystemException
     * @throws Exception
     */
    public function rebuildFromLock(array $versionPrefixes = []): void
    {
        $composerJsonBuilder = new ComposerJsonFromLockBuilder($this->composerJsonPath, $this->composerLockPath);
        $this->updateContents($composerJsonBuilder->versionsFromLock($versionPrefixes));
    }

    /**
     * @throws FilesystemException
     */
    public function updateContents(string $fileContents): void
    {
        file_put_contents($this->composerJsonPath, $fileContents);
    }

    /**
     * @return array<string, string>
     *
     * @throws FilesystemException
     * @throws JsonException
     */
    private function packages(): array
    {
        $composerJson = json_decode(file_get_contents($this->composerJsonPath), true);

        return array_merge($composerJson['require'], $composerJson['require-dev']);
    }

    private function shouldPackageBeUpdated(string $packageName): bool
    {
        return str_contains($packageName, '/') && $packageName !== 'martinsr/constraint-updater';
    }
}
