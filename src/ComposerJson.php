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
        $constraints = [];
        $composerJson = json_decode(file_get_contents($this->composerJsonPath));
        foreach (['require', 'require-dev'] as $requirement) {
            foreach ($composerJson->{$requirement} as $package => $version) {
                if (str_contains($package, '/')) {
                    preg_match('#([\^|~|>|=|<]*)#s', $version, $versionPrefix);
                    $constraints[$package] = $versionPrefix[1];
                }
            }
        }

        return $constraints;
    }

    /**
     * @param array<string, string> $packageConstraints
     *
     * @throws JsonException
     * @throws FilesystemException
     */
    public function replaceVersionsWithAsterisk(array $packageConstraints): void
    {
        $requirements = ['require', 'require-dev'];
        $composerJson = json_decode(file_get_contents($this->composerJsonPath), true);
        foreach ($requirements as $requirement) {
            foreach ($composerJson[$requirement] as $package => $version) {
                if ($this->shouldPackageBeUpdated($package)) {
                    $composerJson[$requirement][$package] = $packageConstraints[$package] ?? '*';
                }
            }
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

    private function shouldPackageBeUpdated(string $packageName): bool
    {
        return str_contains($packageName, '/') && $packageName !== 'martinsr/constraint-updater';
    }
}
