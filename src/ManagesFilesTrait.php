<?php

namespace Ninja\Preloader;

use Closure;
use LogicException;
use Symfony\Component\Finder\Finder;

trait ManagesFilesTrait
{
    protected Finder $appended;
    protected Finder $excluded;
    protected bool $selfExclude = false;

    /**
     * @param string[]|string|Closure|Finder $directories
     */
    public function append(array|string|Closure|Finder $directories): self
    {
        $this->appended = $this->findFiles($directories);

        return $this;
    }

    /**
     * @param string[]|string|Closure|Finder $directories
     */
    public function exclude(array|string|Closure|Finder $directories): self
    {
        $this->excluded = $this->findFiles($directories);

        return $this;
    }

    public function selfExclude(): self
    {
        $this->selfExclude = true;

        return $this;
    }

    /**
     * @param string[]|string|Closure|Finder $files
     */
    protected function findFiles(array|string|Closure|Finder $files): Finder
    {
        if (is_callable($files)) {
            $files($finder = new Finder());
        } else {
            if (is_string($files) || is_array($files)) {
                $finder = (new Finder())->in($files);
            } else {
                $finder = $files;
            }
        }

        return $finder->files();
    }

    /**
     * @return string[]
     */
    protected function getFilesFromFinder(Finder $finder): array
    {
        $paths = [];

        try {
            foreach ($finder as $file) {
                $paths[] = $file->getRealPath();
            }

            return $paths;
        } catch (LogicException $exception) {
            return $paths;
        }
    }
}
