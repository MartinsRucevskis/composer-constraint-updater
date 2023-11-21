<?php

namespace MartinsR\ComposerConstraintUpdater;

class ComposerJsonFromLockBuilder
{
    use FileOpener;
    public function __construct(
        private readonly string $composerJsonPath,
        private readonly string $composerLockPath
    ) {}

    /**
     * @param array<string, array<string, string>> $originalVersionPrefixes
     * @return string
     * @throws \Exception
     */
    public function versionsFromLock(array $originalVersionPrefixes): string
    {
        $composerLockContents = $this->fileContents($this->composerLockPath);
        $composerJsonContents = $this->fileContents($this->composerJsonPath);

        $types = ['require', 'require-dev'];

        foreach ($types as $type) {
            foreach ($this->dependencies($composerJsonContents, $type) as $dependency => $version) {
                preg_match('#"name": "' . preg_quote($dependency) . '",\s+"version": "(.+)"#m', $composerLockContents, $match);

                if (isset($match[1])) {
                    $lockVersion = $originalVersionPrefixes[$type][$dependency] . $match[1];
                    $composerJsonContents = str_replace(
                        '"' . $dependency . '": ' . $version,
                        '"' . $dependency . '": "' . $lockVersion . '"',
                        $composerJsonContents
                    );
                }
            }
        }

        return $composerJsonContents;
    }

    /**
     * @param string $composerJsonContents
     * @param string $dependencyType
     * @return array<string, string>
     */
    private function dependencies(string $composerJsonContents, string $dependencyType): array
    {
        preg_match('/"' . preg_quote($dependencyType) . '":\s+{([\s\S]+?)}/', $composerJsonContents, $dependencies);
        $dependencies = preg_split('/,/m', $dependencies[1]);
        $composerDependencies = [];
        if($dependencies !== false) {
            foreach ($dependencies as &$dependency) {
                $dependency = preg_replace('/\s+/m', '', $dependency);
                $dependency = explode(':', $dependency);
                if (!empty($dependency[0])) {
                    $dependencyName = trim($dependency[0], '"');
                    $composerDependencies[$dependencyName] = $dependency[1];
                }
            }
        }

        return $composerDependencies;
    }
}
