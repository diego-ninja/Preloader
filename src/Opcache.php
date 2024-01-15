<?php

namespace Ninja\Preloader;

use RuntimeException;

class Opcache
{
    /**
     * @var array<string, mixed>|false
     */
    protected array|false $status;

    public function __construct()
    {
        $this->status = opcache_get_status();
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatus(): array
    {
        if ($this->status !== false) {
            return $this->status;
        }

        throw new RuntimeException(
            'Opcache is disabled. Further reference: https://www.php.net/manual/en/opcache.configuration'
        );
    }

    public function isEnabled(): bool
    {
        return $this->getStatus()['opcache_enabled'];
    }

    /**
     * @return array<string, array<string, int>>
     */
    public function getScripts(): array
    {
        return $this->getStatus()['scripts'];
    }

    public function getNumberCachedScripts(): int
    {
        return $this->getStatus()['opcache_statistics']['num_cached_scripts'];
    }

    public function getHits(): mixed
    {
        return $this->getStatus()['opcache_statistics']['hits'];
    }
}
