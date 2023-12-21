<?php

namespace MartinsR\ComposerConstraintUpdater;

use function Safe\file_put_contents;
use function Safe\file_get_contents;

class ComposerJson
{
    private string $composerContents;
    /**
     * @var array<string, array<string, string>>
     */
    private array $versionPrefixes;

    /**
     * @throws \Exception
     */
    public function __construct(private readonly string $composerPath, private readonly string $composerLockPath)
    {
        $this->composerContents = file_get_contents($this->composerPath);
        $this->versionPrefixes = $this->versionPrefixes();
    }

    /**
     * @return array<mixed>
     */
    private function versionPrefixes(): array
    {
        $constraints = [];
        $packages = json_decode($this->composerContents);
        foreach (['require', 'require-dev'] as $requirement) {
            foreach ($packages->{$requirement} as $package => $version) {
                if (str_contains('\\', $package)) {
                    preg_match('#([\^|~|>|=|<]*)#s', $version, $versionPrefix);
                    $constraints[$package] = $versionPrefix[1];
                }
            }
        }

        return $constraints;
    }

    /**
     * @param array<string, string> $packageConstraints
     */
    public function replaceVersions(array $packageConstraints): string
    {
        $requirements = ['require', 'require-dev'];
        $packages = json_decode($this->composerContents, true);
        foreach ($requirements as $requirement) {
            foreach ($packages[$requirement] as $package => $version) {
                $packages[$requirement][$package] = $packageConstraints[$package] ?: '*';
            }
        }
        return json_encode($packages, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function rebuildFromLock(): void
    {
        file_put_contents(
            $this->composerPath,
            (new ComposerJsonFromLockBuilder($this->composerPath, $this->composerLockPath))->versionsFromLock($this->versionPrefixes)
        );
    }
}
