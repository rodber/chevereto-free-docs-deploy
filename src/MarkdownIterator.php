<?php

declare(strict_types=1);

namespace DocsDeploy;

use Chevere\Components\Filesystem\Path;
use Chevere\Interfaces\Filesystem\DirInterface;
use Chevere\Interfaces\Filesystem\PathInterface;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;
use DocsDeploy\Flags;
use Exception;

class MarkdownIterator
{
    private DirInterface $dir;

    private RecursiveDirectoryIterator $dirIterator;

    private RecursiveFilterIterator $filterIterator;

    private RecursiveIteratorIterator $recursiveIterator;

    private array $hierarchy = [];

    /**
     * @var Flags[]
     */
    private array $flagged = [];

    public function __construct(DirInterface $dir)
    {
        $dir->assertExists();
        $this->dir = $dir;
        $this->dirIterator = $this->getRecursiveDirectoryIterator($dir->path()->absolute());
        $this->filterIterator = $this->getRecursiveFilterIterator($this->dirIterator);
        $this->recursiveIterator = new RecursiveIteratorIterator($this->filterIterator);
        try {
            $this->recursiveIterator->rewind();
        } catch (UnexpectedValueException $e) {
            echo 'Unable to rewind iterator: '
                . $e->getMessage() . "\n\n"
                . '🤔 Maybe try with user privileges?';
        }
        $this->iterate();
    }

    public function dir(): DirInterface
    {
        return $this->dir;
    }

    public function hierarchy(): array
    {
        return $this->hierarchy;
    }

    public function flagged(): array
    {
        return $this->flagged;
    }

    private function iterate(): void
    {
        $chop = strlen($this->dir->path()->absolute());
        while ($this->recursiveIterator->valid()) {
            $path = $this->recursiveIterator->current()->getPathName();
            $path = substr($path, $chop);
            $explode = explode('/', $path);
            $root = '/';
            $node = $explode[0];
            if (isset($explode[1])) {
                $root = '/' . $explode[0] . '/';
                $node = substr($path, strlen($root) - 1);
            }
            if ($node == false) {
                $this->recursiveIterator->next();
                continue;
            }
            if (!isset($this->flagged[$root])) {
                $flags = new Flags($root);
            } else {
                $flags = $this->flagged[$root];
            }
            if (strpos($node, '/') !== false) {
                $flags = $flags->withNested(true);
            }
            if ($node === 'README.md') {
                $node = '';
                $flags = $flags->withReadme(true);
            }
            $this->hierarchy[$root][] = $node;
            $this->flagged[$root] = $flags;
            $this->recursiveIterator->next();
        }
    }

    private function getRecursiveDirectoryIterator(string $path): RecursiveDirectoryIterator
    {
        return new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS
            | RecursiveDirectoryIterator::KEY_AS_PATHNAME
        );
    }

    private function getRecursiveFilterIterator(RecursiveDirectoryIterator $dirIterator): RecursiveFilterIterator
    {
        return new class($dirIterator) extends RecursiveFilterIterator {
            public function accept(): bool
            {
                if ($this->hasChildren()) {
                    return true;
                }

                return $this->current()->getExtension() === 'md';
            }
        };
    }
}
