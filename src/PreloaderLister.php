<?php

namespace Ninja\Preloader;

use const DIRECTORY_SEPARATOR;

class PreloaderLister
{
    /**
     * @var array<string, array<string, int>>
     */
    public array $list;
    public float $memory = Preloader::MEMORY_LIMIT;

    public bool $selfExclude = false;

    /**
     * @var string[]
     */
    public array $appended = [];

    /**
     * @var string[]
     */
    public array $excluded = [];

    /**
     * @return string[]
     */
    public function build(): array
    {
        // Exclude "$PRELOAD$" phantom file
        $scripts = $this->excludePreloadVariable($this->list);

        // Exclude the files set by the developer
        $scripts = $this->exclude($scripts);

        // Sort the scripts by hit ratio
        $scripts = $this->sortScripts($scripts);

        // Cull the list by memory usage
        $scripts = $this->cutByMemoryLimit($scripts);

        // Add files to the preload.
        $scripts = array_merge($scripts, $this->appended);

        // Remove duplicates and return
        return array_unique($scripts);
    }

    /**
     * @param array<string, array<string, int>> $list
     * @return array<string, array<string, int>>
     */
    protected function excludePreloadVariable(array $list): array
    {
        unset($list['$PRELOAD$']);

        return $list;
    }

    /**
     * @param array<string, array<string, int>> $scripts
     * @return array<string, array<string, int>>
     */
    protected function sortScripts(array $scripts): array
    {
        // There is no problem here with the Preloader.
        array_multisort(
            /** @phpstan-ignore-next-line */
            array_column($scripts, 'hits'),
            SORT_DESC,
            array_column($scripts, 'last_used_timestamp'),
            SORT_DESC,
            $scripts
        );

        return $scripts;
    }

    /**
     * @param array<string, array<string, int>> $files
     * @return string[]
     */
    protected function cutByMemoryLimit(array $files): array
    {
        // Exit early if the memory limit is zero (disabled).
        if (!$limit = $this->memory * 1024 ** 2) {
            return array_keys($files);
        }

        $cumulative = 0;

        $resulting = [];

        // We will cycle through each file and check how much memory it consumes. If adding
        // the file exceeds the memory limit set, we will stop adding files to the compiled
        // list of preloaded files. Otherwise, we'll keep cycling and return all the files.
        foreach ($files as $key => $file) {
            $cumulative += $file['memory_consumption'];

            if ($cumulative > $limit) {
                return $resulting;
            }

            $resulting[] = $key;
        }

        return $resulting;
    }

    /**
     * @param array<string, array<string, int>> $scripts
     * @return array<string, array<string, int>>
     */
    protected function exclude(array $scripts): array
    {
        return array_diff_key(
            $scripts,
            array_flip(
                array_merge(
                    $this->excluded,
                    $this->excludedPackageFiles()
                )
            )
        );
    }

    /**
     * @return string[]
     */
    protected function excludedPackageFiles(): array
    {
        $excluded = [];
        if ($this->selfExclude) {
            $current = realpath(__DIR__);
            $files   = glob($current . DIRECTORY_SEPARATOR . '*.php');

            if ($files === false) {
                return [];
            }

            foreach ($files as $file) {
                if ($filePath = realpath($file)) {
                    $excluded[] = $filePath;
                }
            }
        }

        return $excluded;
    }
}
