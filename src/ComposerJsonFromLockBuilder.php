<?php

namespace MartinsR\ComposerConstraintUpdater;

class ComposerJsonFromLockBuilder
{
    public function __construct(
        private readonly string $composerJsonPath,
        private readonly string $composerLockPath
    ){}

    public function versionsFromLock()
    {
        $composerLockContents = file_get_contents($this->composerLockPath) 
            ?: throw new \Exception('Couldn\'t open composer lock file from ' . $this->composerLockPath);
        $composerJsonContents = file_get_contents($this->composerJsonPath) 
            ?: throw new \Exception('Couldn\'t open composer json file from ' . $this->composerJsonPath);

        $types = ['require', 'require-dev'];
        
        foreach ($types as $type) {
            foreach ($this->dependencies($composerJsonContents, $type) as $dependency => $version) {
                preg_match('#"name": ' . preg_quote($dependency) . ',\s+"version": "(.+)"#m', $composerLockContents, $match);
                
                if (isset($match[1])) {
                    $lockVersion = str_contains($match[1],'dev')? $match[1] : '^'.$match[1]; 
                    $composerJsonContents = str_replace(
                        $dependency . ': ' . $version,
                        $dependency . ': "' . $lockVersion . '"',
                        $composerJsonContents
                    );
                }
            }
        }

        return $composerJsonContents;
    }

    private
    function dependencies(string $composerJsonContents, string $dependencyType): array
    {
        preg_match('/"' . preg_quote($dependencyType) . '":\s+{([\s\S]+?)}/', $composerJsonContents, $dependencies);
        $dependencies = preg_split('/,/ms', $dependencies[1]);
        $composerDependencies = [];
        foreach ($dependencies as &$dependency) {
            $dependency = preg_replace('/\s+/ms', '', $dependency);
            $dependency = explode(':', $dependency);
            $composerDependencies[$dependency[0]] = $dependency[1];
        }

        return $composerDependencies;
    }
}