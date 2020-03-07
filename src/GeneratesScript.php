<?php

namespace DarkGhostHunter\Preloader;

trait GeneratesScript
{
    /**
     * Memory limit (in MB).
     *
     * @var float
     */
    protected $memory = self::MEMORY_LIMIT;

    /**
     * Memory limit (in MB) to constrain the file list.
     *
     * @param  int|float $limit
     * @return $this
     *
     * @throws \RuntimeException
     */
    public function memory($limit)
    {
        $this->memory = (float)$limit;

        return $this;
    }

    /**
     * Returns a digestible Opcache configuration
     *
     * @return array
     */
    protected function getOpcacheConfig()
    {
        $opcache = $this->opcache->getStatus();

        return [
            '@opcache_memory_used' =>
                number_format($opcache['memory_usage']['used_memory'] / 1024**2, 1, '.', ''),
            '@opcache_memory_free' =>
                number_format($opcache['memory_usage']['free_memory'] / 1024**2, 1, '.', ''),
            '@opcache_memory_wasted' =>
                number_format($opcache['memory_usage']['wasted_memory'] / 1024**2, 1, '.', ''),
            '@opcache_files' => $opcache['opcache_statistics']['num_cached_scripts'],
            '@opcache_hit_rate' => number_format($opcache['opcache_statistics']['opcache_hit_rate'], 2, '.', ''),
            '@opcache_misses' => $opcache['opcache_statistics']['misses'],
        ];
    }

    /**
     * Returns a digestible Preloader configuration
     *
     * @return array
     */
    protected function getPreloaderConfig()
    {
        return [
            '@preloader_memory_limit' => $this->lister->memory,
            '@preloader_overwrite' => $this->overwrite ? 'true' : 'false',
            '@preloader_appended' => count($this->lister->append),
            '@preloader_excluded' => count($this->lister->exclude),
        ];
    }
}
