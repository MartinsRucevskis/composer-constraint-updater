<?php

namespace MartinsR\ComposerConstraintUpdater;

class ComposerJson
{
    use FileOpener;
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
        $this->composerContents = $this->fileContents($this->composerPath);
        $this->versionPrefixes = $this->versionPrefixes();
    }

    /**
     * @return array<mixed>
     */
    private function versionPrefixes(): array
    {
        $constraints = [];
        foreach (['require', 'require-dev'] as $requirement) {
            preg_match('/"' . preg_quote($requirement) . '"\s*:\s*\{([\s|\S]+?)\}/m', $this->composerContents, $matches);
            if (isset($matches[1])) {
                preg_match_all('/\"(.+?)\"\s*:\s*"([<>~^=]*).*"/m', $matches[1], $matches);
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $constraints[$requirement][$matches[1][$i]] = $matches[2][$i] ?? '^';
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
        foreach ($requirements as $requirement) {
            preg_match('/"' . preg_quote($requirement) . '"\s*:\s*\{([\s|\S]+?)\}/m', $this->composerContents, $matches);
            $packages = preg_replace('/:\s*"(?!.*dev)(.*)+?"/m', ': "*"', $matches[1]);
            foreach ($packageConstraints as $constraint => $version) {
                $packages = preg_replace(
                    '@"' . preg_quote($constraint) . '"\s*:\s*"\*"@m',
                    '"' . $constraint . '": "' . $version . '"',
                    $packages
                );
            }
            $this->composerContents = preg_replace('/"' . preg_quote($requirement) . '"\s*:\s*\{([\s|\S]+?)\}/m', '"' . $requirement . '": {' . $packages . '}', $this->composerContents);
        }

        return $this->composerContents;
    }

    public function rebuildFromLock(): string
    {
        return (new ComposerJsonFromLockBuilder($this->composerPath, $this->composerLockPath))->versionsFromLock($this->versionPrefixes);
    }
}
